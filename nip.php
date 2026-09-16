<?php
declare(strict_types=1);

/*
 * Zwraca nazwę i adres firmy dla podanego numeru NIP.
 *
 * Pyta po kolei dwa rejestry:
 *   1. Wykaz podatników VAT Ministerstwa Finansów — bez klucza, obejmuje spółki
 *      i jednoosobowe działalności zarejestrowane do VAT.
 *   2. CEIDG — jednoosobowe działalności, także te zwolnione z VAT, których
 *      w wykazie MF nie ma.
 *
 * WGRANIE NA ZENBOX
 * 1. Wrzuć plik obok formularz.php, tak żeby działał pod
 *    https://www.officeinfluencers.pl/nip.php
 * 2. CEIDG wymaga własnego tokenu. Załóż konto na https://dane.biznes.gov.pl,
 *    wygeneruj token i wklej go w stałą CEIDG_TOKEN poniżej.
 *    Bez tokenu skrypt działa dalej, tylko pomija CEIDG i korzysta z wykazu MF.
 * 3. Token trzymamy po stronie serwera. W kodzie strony nie może się pojawić,
 *    bo każdy odwiedzający mógłby go odczytać i wykorzystać.
 */

const CEIDG_TOKEN = '';           // <— tutaj wklej token z dane.biznes.gov.pl
const LIMIT_CZASU = 6;            // sekundy na odpowiedź rejestru

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store');

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

/** Wykaz podatników VAT: zwraca nazwę i adres prowadzenia działalności. */
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

/** CEIDG: jednoosobowe działalności; wymaga tokenu. */
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
