<?php
declare(strict_types=1);

require __DIR__ . '/bezpieczenstwo.php';

/*
 * Nazwa i adres firmy po numerze NIP, z wykazu podatników VAT Ministerstwa
 * Finansów. Adres rejestru jest stały, a NIP to same cyfry, więc skryptu
 * nie da się użyć do odpytywania innych serwerów.
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

function pobierz(string $url): ?array
{
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => LIMIT_CZASU,
            CURLOPT_HTTPHEADER     => ['Accept: application/json'],
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
                'header'          => 'Accept: application/json',
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

    if ($kod !== 200 || !is_string($tresc) || $tresc === '') {
        return null;
    }
    $dane = json_decode($tresc, true);
    return is_array($dane) ? $dane : null;
}

/*
 * Dane z rejestru trafiają prosto w pola formularza i dalej w wiadomość.
 * Obcinamy je do rozsądnej długości i czyścimy ze znaków, których nie widać.
 */
function zRejestru(string $wartosc, int $limit): string
{
    return trim(mb_substr(bezZnacznikow(bezZnakowSterujacych($wartosc)), 0, $limit));
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

$dane    = pobierz('https://wl-api.mf.gov.pl/api/search/nip/' . $nip . '?date=' . date('Y-m-d'));
$podmiot = $dane['result']['subject'] ?? null;
if (!is_array($podmiot) || empty($podmiot['name']) || !is_string($podmiot['name'])) {
    odpowiedz(['ok' => false, 'komunikat' => 'Nie znaleziono firmy o tym numerze NIP.'], 404);
}

$adres = $podmiot['workingAddress'] ?? $podmiot['residenceAddress'] ?? '';
odpowiedz([
    'ok'     => true,
    'nazwa'  => zRejestru($podmiot['name'], 160),
    'adres'  => zRejestru(is_string($adres) ? $adres : '', 300),
    'zrodlo' => 'wykazu podatników VAT',
]);
