<?php
declare(strict_types=1);

require __DIR__ . '/bezpieczenstwo.php';

/*
 * Nazwa i adres firmy po numerze NIP. Najpierw wykaz podatników VAT
 * Ministerstwa Finansów (spółki i firmy z VAT), potem CEIDG z Hurtowni Danych
 * (jednoosobowe działalności, także bez VAT), jeśli w klucze.php jest token.
 * Adresy rejestrów są stałe, a NIP to same cyfry, więc skryptu nie da się
 * użyć do odpytywania innych serwerów.
 */
const LIMIT_CZASU    = 6;
const MAX_ODPOWIEDZI = 200000;

const LIMIT_ZAPYTAN = 25;
const OKNO_LIMITU   = 3600;
const LIMIT_SERII   = 6;
const OKNO_SERII    = 120;

header('Content-Type: application/json; charset=utf-8');
naglowkiBezpieczenstwa();

function odpowiedz(array $dane, int $kod = 200): void
{
    http_response_code($kod);
    echo json_encode($dane, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP);
    exit;
}

const CEIDG_ADRESY = [
    'https://dane.biznes.gov.pl/api/ceidg/v3/firma',
    'https://dane.biznes.gov.pl/api/ceidg/v3/firmy',
];

/** Zwraca [kod HTTP, dane JSON albo null]. */
function pobierz(string $url, array $naglowki = []): array
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
            CURLOPT_MAXFILESIZE    => MAX_ODPOWIEDZI,
        ]);
        $tresc = curl_exec($ch);
        $kod   = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);
    } else {
        $kontekst = stream_context_create([
            'http' => [
                'method'          => 'GET',
                'header'          => implode("\r\n", $naglowki),
                'timeout'         => LIMIT_CZASU,
                'follow_location' => 0,
                'ignore_errors'   => true,
            ],
            'ssl' => ['verify_peer' => true, 'verify_peer_name' => true],
        ]);
        $tresc = @file_get_contents($url, false, $kontekst, 0, MAX_ODPOWIEDZI);
        $kod   = 0;
        foreach ($http_response_header ?? [] as $naglowek) {
            if (preg_match('#^HTTP/\S+\s+(\d{3})#', $naglowek, $m)) {
                $kod = (int)$m[1];
            }
        }
    }

    $dane = is_string($tresc) && $tresc !== '' ? json_decode($tresc, true) : null;
    return [$kod, is_array($dane) ? $dane : null];
}

/*
 * Dane z rejestru trafiają prosto w pola formularza i dalej w wiadomość.
 * Obcinamy je do rozsądnej długości i czyścimy ze znaków, których nie widać.
 */
function zRejestru(string $wartosc, int $limit): string
{
    return trim(mb_substr(bezZnacznikow(bezZnakowSterujacych($wartosc)), 0, $limit));
}

function zWykazuVat(string $nip): ?array
{
    [$kod, $dane] = pobierz('https://wl-api.mf.gov.pl/api/search/nip/' . $nip . '?date=' . date('Y-m-d'));
    $podmiot = $kod === 200 ? ($dane['result']['subject'] ?? null) : null;
    if (!is_array($podmiot) || empty($podmiot['name']) || !is_string($podmiot['name'])) {
        return null;
    }
    $adres = $podmiot['workingAddress'] ?? $podmiot['residenceAddress'] ?? '';
    return [
        'nazwa'  => zRejestru($podmiot['name'], 160),
        'adres'  => zRejestru(is_string($adres) ? $adres : '', 300),
        'zrodlo' => 'wykazu podatników VAT',
    ];
}

/** Token z klucze.php: tylko znaki, które może mieć token JWT. */
function tokenCeidg(): string
{
    $plik = __DIR__ . '/klucze.php';
    if (!is_file($plik)) {
        return '';
    }
    if (!defined('AIOL_KLUCZE')) {
        define('AIOL_KLUCZE', true);
    }
    $klucze = require $plik;
    $token  = is_array($klucze) ? trim((string)($klucze['ceidg_token'] ?? '')) : '';
    return preg_match('/^[A-Za-z0-9._-]{20,4000}$/', $token) ? $token : '';
}

function tekst($wartosc): string
{
    return is_scalar($wartosc) ? trim((string)$wartosc) : '';
}

/*
 * CEIDG zwraca listę wpisów pod kluczem "firma" albo "firmy". Jeden NIP może
 * mieć kilka wpisów (np. stary wykreślony), więc bierzemy aktywny.
 */
function zCeidg(string $nip): ?array
{
    $token = tokenCeidg();
    if ($token === '') {
        return null;
    }
    foreach (CEIDG_ADRESY as $adres) {
        [$kod, $dane] = pobierz($adres . '?nip=' . $nip, ['Authorization: Bearer ' . $token]);
        if ($kod === 404) {
            continue; // brak wpisu albo inna ścieżka API: próbujemy następnej
        }
        if ($kod !== 200) {
            if ($kod !== 204) {
                // 401 lub 403 oznacza zły albo wygasły token w klucze.php
                error_log('CEIDG: odpowiedz HTTP ' . $kod);
            }
            return null;
        }
        $wpisy = $dane['firma'] ?? $dane['firmy'] ?? [];
        if (is_array($wpisy) && isset($wpisy['nazwa'])) {
            $wpisy = [$wpisy]; // pojedynczy wpis zamiast listy
        }
        if (!is_array($wpisy) || $wpisy === []) {
            return null;
        }
        usort($wpisy, static function ($a, $b): int {
            $waga = ['AKTYWNY' => 0, 'ZAWIESZONY' => 1];
            return ($waga[tekst($a['status'] ?? '')] ?? 2) <=> ($waga[tekst($b['status'] ?? '')] ?? 2);
        });
        $firma = is_array($wpisy[0]) ? $wpisy[0] : [];
        $nazwa = tekst($firma['nazwa'] ?? '');
        if ($nazwa === '') {
            return null;
        }
        $a = $firma['adresDzialalnosci'] ?? $firma['adresDzialanosci'] ?? $firma['adresKorespondencyjny'] ?? [];
        $a = is_array($a) ? $a : [];
        $lokal  = tekst($a['lokal'] ?? '');
        $ulica  = trim(tekst($a['ulica'] ?? '') . ' ' . tekst($a['budynek'] ?? '') . ($lokal !== '' ? '/' . $lokal : ''));
        $miasto = trim(tekst($a['kodPocztowy'] ?? $a['kod'] ?? '') . ' ' . tekst($a['miasto'] ?? ''));
        return [
            'nazwa'  => zRejestru($nazwa, 160),
            'adres'  => zRejestru(implode(', ', array_filter([$ulica, $miasto], 'strlen')), 300),
            'zrodlo' => 'CEIDG',
        ];
    }
    return null;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
    odpowiedz(['ok' => false, 'komunikat' => 'Dozwolona jest tylko metoda GET.'], 405);
}

sprawdzMetadanePobrania(static function (): void {
    odpowiedz(['ok' => false, 'komunikat' => 'Żądanie spoza strony officeinfluencers.pl.'], 403);
});

sprawdzPochodzenie(false, static function (): void {
    odpowiedz(['ok' => false, 'komunikat' => 'Żądanie spoza strony officeinfluencers.pl.'], 403);
});

if (array_keys($_GET) !== ['nip'] || !is_string($_GET['nip'])) {
    odpowiedz(['ok' => false, 'komunikat' => 'Nieznane zapytanie.'], 400);
}

/*
 * Dwa liczniki, żeby nikt nie przepisał sobie rejestru firm przez ten
 * skrypt. Wypełniając formularz, pyta się raz, może dwa razy.
 */
limitZadan('nip-seria', LIMIT_SERII, OKNO_SERII, static function (): void {
    odpowiedz(['ok' => false, 'komunikat' => 'Zbyt wiele zapytań. Wpisz nazwę i adres ręcznie.'], 429);
});
limitZadan('nip', LIMIT_ZAPYTAN, OKNO_LIMITU, static function (): void {
    odpowiedz(['ok' => false, 'komunikat' => 'Zbyt wiele zapytań. Wpisz nazwę i adres ręcznie.'], 429);
});

$nip = $_GET['nip'];
if (!preg_match('/^\d{10}$/', $nip) || !nipPoprawny($nip)) {
    odpowiedz(['ok' => false, 'komunikat' => 'NIP ma 10 cyfr.'], 400);
}

// Wykaz VAT nie zna jednoosobowych działalności bez VAT i czasem nie podaje adresu.
// Wtedy pytamy CEIDG.
$wynik = zWykazuVat($nip);
if ($wynik === null || $wynik['adres'] === '') {
    $ceidg = zCeidg($nip);
    if ($ceidg !== null && ($wynik === null || $ceidg['adres'] !== '')) {
        $wynik = $ceidg;
    }
}
if ($wynik === null) {
    odpowiedz(['ok' => false, 'komunikat' => 'Nie znaleziono firmy o tym numerze NIP.'], 404);
}
odpowiedz(['ok' => true] + $wynik);
