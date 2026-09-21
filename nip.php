<?php
declare(strict_types=1);

require __DIR__ . '/bezpieczenstwo.php';

const CEIDG_TOKEN = '';
const LIMIT_CZASU = 6;

const LIMIT_ZAPYTAN = 40;
const OKNO_LIMITU   = 3600;

header('Content-Type: application/json; charset=utf-8');
naglowkiBezpieczenstwa();

function odpowiedz(array $dane, int $kod = 200): void
{
    http_response_code($kod);
    echo json_encode($dane, JSON_UNESCAPED_UNICODE);
    exit;
}

function pobierz(string $url, array $naglowki = []): ?array
{
    $naglowki[] = 'Accept: application/json';

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => LIMIT_CZASU,
            CURLOPT_HTTPHEADER     => $naglowki,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_PROTOCOLS      => CURLPROTO_HTTPS,
        ]);
        $tresc = curl_exec($ch);
        $kod   = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);
    } else {
        $kontekst = stream_context_create(['http' => [
            'method'        => 'GET',
            'header'        => implode("\r\n", $naglowki),
            'timeout'       => LIMIT_CZASU,
            'ignore_errors' => true,
        ]]);
        $tresc = @file_get_contents($url, false, $kontekst);
        $kod   = 0;
        foreach ($http_response_header ?? [] as $naglowek) {
            if (preg_match('#^HTTP/\S+\s+(\d{3})#', $naglowek, $m)) {
                $kod = (int)$m[1];
            }
        }
    }

    if ($kod !== 200 || !is_string($tresc) || $tresc === '') {
        return null;
    }
    $dane = json_decode($tresc, true);
    return is_array($dane) ? $dane : null;
}

function zMinisterstwaFinansow(string $nip): ?array
{
    $dane = pobierz(sprintf(
        'https://wl-api.mf.gov.pl/api/search/nip/%s?date=%s',
        $nip,
        date('Y-m-d')
    ));
    $podmiot = $dane['result']['subject'] ?? null;
    if (!is_array($podmiot) || empty($podmiot['name'])) {
        return null;
    }
    return [
        'nazwa'  => (string)$podmiot['name'],
        'adres'  => (string)($podmiot['workingAddress'] ?? $podmiot['residenceAddress'] ?? ''),
        'zrodlo' => 'wykazu podatników VAT',
    ];
}

function zCeidg(string $nip): ?array
{
    if (CEIDG_TOKEN === '') {
        return null;
    }
    $dane = pobierz(
        'https://dane.biznes.gov.pl/api/ceidg/v3/firmy?nip=' . $nip,
        ['Authorization: Bearer ' . CEIDG_TOKEN]
    );
    $firma = $dane['firmy'][0] ?? null;
    if (!is_array($firma)) {
        return null;
    }
    $nazwa = (string)($firma['nazwa'] ?? '');
    if ($nazwa === '') {
        return null;
    }

    $a = $firma['adresDzialalnosci'] ?? $firma['adresKorespondencyjny'] ?? [];
    $ulica = trim(implode(' ', array_filter([
        (string)($a['ulica'] ?? ''),
        (string)($a['budynek'] ?? ''),
        ($a['lokal'] ?? '') !== '' ? '/' . $a['lokal'] : '',
    ])));
    $miasto = trim((string)($a['kod'] ?? '') . ' ' . (string)($a['miasto'] ?? ''));
    $adres  = trim(implode(', ', array_filter([$ulica, $miasto])), ', ');

    return ['nazwa' => $nazwa, 'adres' => $adres, 'zrodlo' => 'CEIDG'];
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
    odpowiedz(['ok' => false, 'komunikat' => 'Dozwolona jest tylko metoda GET.'], 405);
}

sprawdzPochodzenie(false, static function (): void {
    odpowiedz(['ok' => false, 'komunikat' => 'Żądanie spoza strony officeinfluencers.pl.'], 403);
});

limitZadan('nip', LIMIT_ZAPYTAN, OKNO_LIMITU, static function (): void {
    odpowiedz(['ok' => false, 'komunikat' => 'Zbyt wiele zapytań. Wpisz nazwę i adres ręcznie.'], 429);
});

$nip = preg_replace('/\D/', '', (string)($_GET['nip'] ?? '')) ?? '';
if (strlen($nip) !== 10) {
    odpowiedz(['ok' => false, 'komunikat' => 'NIP ma 10 cyfr.'], 400);
}

foreach (['zMinisterstwaFinansow', 'zCeidg'] as $rejestr) {
    $wynik = $rejestr($nip);
    if ($wynik !== null) {
        odpowiedz(['ok' => true] + $wynik);
    }
}

odpowiedz(['ok' => false, 'komunikat' => 'Nie znaleziono firmy o tym numerze NIP.'], 404);
