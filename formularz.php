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
    odpowiedz(400, 'Podaj poprawny adres e-mail.');
}

$poleImienia = $typ === 'zgloszenie' ? 'imie_nazwisko' : 'imie';
if (mb_strlen(wartosc($poleImienia)) < 2) {
    odpowiedz(400, 'Podaj imię i nazwisko.');
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
            odpowiedz(400, 'NIP ma 10 cyfr.');
        }
        if (wartosc('firma') === '' || wartosc('adres_faktury') === '') {
            odpowiedz(400, 'Uzupełnij dane do faktury.');
        }
    }
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
