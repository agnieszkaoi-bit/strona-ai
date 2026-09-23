<?php
declare(strict_types=1);

require __DIR__ . '/bezpieczenstwo.php';

const ODBIORCY = [
    'zgloszenie' => 'office@officeinfluencers.pl',
    'oferta'     => 'agnieszka.korach@arkconsulting.com.pl',
];

const TEMATY = [
    'zgloszenie' => 'Zgłoszenie na szkolenie APZ',
    'oferta'     => 'Zapytanie dot. szkolenia APZ',
];

const NADAWCA      = 'office@officeinfluencers.pl';
const NAZWA_NADAWCY = 'Formularz officeinfluencers.pl';

const POLA = [
    'zgloszenie' => [
        'imie_nazwisko'        => 'Imię i nazwisko',
        'email'                => 'E-mail',
        'telefon'              => 'Telefon',
        'stanowisko'           => 'Stanowisko',
        'liczba_osob'          => 'Liczba osób',
        'platnik'              => 'Płatnik',
        'firma'                => 'Firma',
        'nip'                  => 'NIP',
        'adres_faktury'        => 'Adres do faktury',
        'uwagi'                => 'Uwagi',
        'zgoda_rodo'           => 'Zgoda na przetwarzanie danych',
        'zgoda_marketing'      => 'Zgoda marketingowa',
    ],
    'oferta' => [
        'imie'  => 'Imię',
        'email' => 'E-mail',
    ],
];

const MAILERLITE_TOKEN = '';
const MAILERLITE_GRUPA = '';

const MAX_DLUGOSC = 2000;

const LIMIT_WYSLANYCH = 5;
const LIMIT_ZADAN     = 30;
const OKNO_LIMITU     = 3600;

const MIN_CZAS_MS = 3000;

const PLATNICY      = ['firma', 'osoba_prywatna'];

const DOMENY_JEDNORAZOWE = [
    'mailinator.com', 'guerrillamail.com', 'guerrillamail.info', '10minutemail.com',
    'tempmail.com', 'temp-mail.org', 'yopmail.com', 'trashmail.com', 'getnada.com',
    'sharklasers.com', 'throwawaymail.com', 'maildrop.cc', 'dispostable.com',
    'fakeinbox.com', 'mailnesia.com', 'mohmal.com', 'tempr.email', 'emailondeck.com',
];

const WAGI_NIP = [6, 5, 7, 2, 3, 4, 5, 6, 7];
const MAX_UCZESTNIKOW = 20;

header('Content-Type: application/json; charset=utf-8');
naglowkiBezpieczenstwa();

function odpowiedz(int $kod, string $komunikat): void
{
    http_response_code($kod);
    echo json_encode(
        ['ok' => $kod === 200, 'komunikat' => $komunikat],
        JSON_UNESCAPED_UNICODE
    );
    exit;
}

function bezNaglowkow(string $wartosc): string
{
    return trim(str_replace(["\r", "\n", "\0"], ' ', $wartosc));
}

/*
 * NIP ma cyfrę kontrolną. Sprawdzenie jej wyłapuje literówkę bez pytania
 * rejestru i odsiewa numery wpisane na chybił trafił.
 */
function nipPoprawny(string $cyfry): bool
{
    if (strlen($cyfry) !== 10 || preg_match('/^(\d)\1{9}$/', $cyfry)) {
        return false;
    }
    $suma = 0;
    for ($i = 0; $i < 9; $i++) {
        $suma += WAGI_NIP[$i] * (int)$cyfry[$i];
    }
    $kontrolna = $suma % 11;
    return $kontrolna !== 10 && $kontrolna === (int)$cyfry[9];
}

/** Ile adresów internetowych siedzi w tekście. */
function ileOdnosnikow(string $tekst): int
{
    return preg_match_all('#(https?://|www\.|\[url|\bhref\s*=)#i', $tekst);
}

/*
 * Pole na imię ma zawierać imię. Adres internetowy albo pismo spoza alfabetu
 * łacińskiego oznacza wpis maszynowy, nie osobę zgłaszającą się na szkolenie
 * prowadzone po polsku.
 */
function imieWygladaNaSpam(string $imie): bool
{
    if (ileOdnosnikow($imie) > 0) {
        return true;
    }
    return (bool)preg_match('/[\p{Cyrillic}\p{Han}\p{Arabic}\p{Hebrew}\p{Hiragana}\p{Katakana}]/u', $imie);
}

/*
 * Czy domena adresu ma serwer pocztowy. Wyłapuje literówki i domeny zmyślone.
 *
 * Najpierw upewniamy się, że odpytywanie DNS w ogóle na tym serwerze działa.
 * Gdyby padło, sprawdzenie odrzucałoby każdy adres i formularz przestałby
 * przyjmować zgłoszenia. W razie wątpliwości przepuszczamy.
 */
function domenaPrzyjmujePoczte(string $email): bool
{
    $domena = substr(strrchr($email, '@') ?: '', 1);
    if ($domena === '' || !function_exists('checkdnsrr')) {
        return true;
    }
    if (!checkdnsrr('gmail.com', 'MX')) {
        error_log('Sprawdzanie DNS nie dziala, pomijam kontrole domeny adresu.');
        return true;
    }
    return checkdnsrr($domena, 'MX') || checkdnsrr($domena, 'A');
}

function wartosc(string $klucz): string
{
    $surowa = $_POST[$klucz] ?? '';
    if (!is_string($surowa)) {
        return '';
    }
    return trim(bezZnakowSterujacych(mb_substr($surowa, 0, MAX_DLUGOSC)));
}

function rozbijImie(string $pelne): array
{
    $czesci = preg_split('/\s+/', trim($pelne), 2) ?: [];
    return [$czesci[0] ?? '', $czesci[1] ?? ''];
}

function daneDoMailerLite(string $email, string $pelneImie): array
{
    [$imie, $nazwisko] = rozbijImie($pelneImie);
    $dane = ['email' => $email, 'fields' => ['name' => $imie]];
    if ($nazwisko !== '') {
        $dane['fields']['last_name'] = $nazwisko;
    }
    if (MAILERLITE_GRUPA !== '') {
        $dane['groups'] = [MAILERLITE_GRUPA];
    }
    return $dane;
}

function doMailerLite(string $email, string $pelneImie): void
{
    if (MAILERLITE_TOKEN === '' || !function_exists('curl_init')) {
        return;
    }
    $ch = curl_init('https://connect.mailerlite.com/api/subscribers');
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode(daneDoMailerLite($email, $pelneImie), JSON_UNESCAPED_UNICODE),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_PROTOCOLS      => CURLPROTO_HTTPS,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer ' . MAILERLITE_TOKEN,
            'Content-Type: application/json',
            'Accept: application/json',
        ],
    ]);
    $odpowiedz = curl_exec($ch);
    $kod = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);

    if ($kod < 200 || $kod > 299) {
        error_log('MailerLite odrzucil zapis (' . $kod . '): ' . substr((string)$odpowiedz, 0, 300));
    }
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    odpowiedz(405, 'Dozwolona jest tylko metoda POST.');
}

sprawdzPochodzenie(true, static function (): void {
    odpowiedz(403, 'Żądanie spoza strony officeinfluencers.pl.');
});

limitZadan('formularz-proby', LIMIT_ZADAN, OKNO_LIMITU, static function (): void {
    odpowiedz(429, 'Zbyt wiele prób z tego adresu. Spróbuj za godzinę lub napisz na office@officeinfluencers.pl.');
});

if (wartosc('www') !== '') {
    odpowiedz(200, 'Dziękuję.');
}
if ((int)wartosc('czas') < MIN_CZAS_MS) {
    odpowiedz(200, 'Dziękuję.');
}

$typ = wartosc('formularz');
if (!isset(ODBIORCY[$typ])) {
    odpowiedz(400, 'Nieznany formularz.');
}

$email = bezNaglowkow(wartosc('email'));
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    odpowiedz(400, 'Wpisz adres w formacie imie@firma.pl.');
}

$domena = strtolower(substr(strrchr($email, '@') ?: '', 1));
if (in_array($domena, DOMENY_JEDNORAZOWE, true)) {
    odpowiedz(400, 'Na ten adres nie wyślę potwierdzenia. Podaj adres, z którego korzystasz na co dzień.');
}
if (!domenaPrzyjmujePoczte($email)) {
    odpowiedz(400, 'Nie znalazłam serwera pocztowego domeny ' . $domena . '. Sprawdź, czy adres nie ma literówki.');
}

$poleImienia = $typ === 'zgloszenie' ? 'imie_nazwisko' : 'imie';
$imie = wartosc($poleImienia);
if (mb_strlen($imie) < 2) {
    odpowiedz(400, 'Wpisz imię i nazwisko.');
}
// Wpis maszynowy kwitujemy uprzejmie i nie wysyłamy nic dalej.
if (imieWygladaNaSpam($imie)) {
    odpowiedz(200, 'Dziękuję.');
}
if ($typ === 'zgloszenie' && !preg_match('/\s/', trim($imie))) {
    odpowiedz(400, 'Wpisz imię i nazwisko, nie samo imię.');
}

if ($typ === 'zgloszenie') {
    if (wartosc('zgoda_rodo') === '') {
        odpowiedz(400, 'Brak zgody na przetwarzanie danych.');
    }
    if (!in_array(wartosc('platnik'), PLATNICY, true)) {
        odpowiedz(400, 'Wskaż, kto opłaca udział.');
    }
    $osoby = (int)wartosc('liczba_osob');
    if ($osoby < 1 || $osoby > MAX_UCZESTNIKOW) {
        odpowiedz(400, 'Podaj liczbę osób od 1 do ' . MAX_UCZESTNIKOW . '.');
    }
    if (wartosc('platnik') === 'firma') {
        $nip = preg_replace('/\D/', '', wartosc('nip')) ?? '';
        if (strlen($nip) !== 10) {
            odpowiedz(400, 'NIP ma dziesięć cyfr, bez myślników i spacji.');
        }
        if (!nipPoprawny($nip)) {
            odpowiedz(400, 'Ten numer nie jest poprawnym NIP-em. Sprawdź, czy cyfry się zgadzają.');
        }
        if (wartosc('firma') === '' || wartosc('adres_faktury') === '') {
            odpowiedz(400, 'Uzupełnij nazwę firmy i adres do faktury.');
        }
    }
}

// Jeden odnośnik w uwagach bywa uzasadniony, kilka to rozsyłka reklamowa.
if (ileOdnosnikow(wartosc('uwagi')) > 1) {
    odpowiedz(200, 'Dziękuję.');
}

$linie = [];
foreach (POLA[$typ] as $klucz => $etykieta) {
    $v = wartosc($klucz);
    if ($v !== '') {
        $linie[] = $etykieta . ': ' . $v;
    }
}
$linie[] = '';
$linie[] = 'Wysłano: ' . date('Y-m-d H:i:s');
$linie[] = 'Strona: ' . bezNaglowkow((string)($_SERVER['HTTP_REFERER'] ?? 'brak'));

$naglowki = implode("\r\n", [
    'From: ' . mb_encode_mimeheader(NAZWA_NADAWCY, 'UTF-8') . ' <' . NADAWCA . '>',
    'Reply-To: ' . $email,
    'MIME-Version: 1.0',
    'Content-Type: text/plain; charset=UTF-8',
    'Content-Transfer-Encoding: 8bit',
]);

limitZadan('formularz-wyslane', LIMIT_WYSLANYCH, OKNO_LIMITU, static function (): void {
    odpowiedz(429, 'Z tego adresu wysłano już kilka zgłoszeń. Napisz na office@officeinfluencers.pl, a dopiszę pozostałe osoby.');
});

$wyslano = mail(
    ODBIORCY[$typ],
    mb_encode_mimeheader(TEMATY[$typ], 'UTF-8'),
    implode("\n", $linie),
    $naglowki,
    '-f' . NADAWCA
);

if (!$wyslano) {
    odpowiedz(500, 'Serwer pocztowy odrzucił wiadomość.');
}

if ($typ === 'zgloszenie' && wartosc('zgoda_marketing') !== '') {
    doMailerLite($email, wartosc('imie_nazwisko'));
}

odpowiedz(200, 'Dziękuję, wiadomość została wysłana.');
