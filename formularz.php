<?php
declare(strict_types=1);

/*
 * Odbiera zgłoszenia z landing page i wysyła je mailem.
 *
 * WGRANIE NA ZENBOX
 * 1. Wrzuć ten plik do katalogu głównego strony (tam, gdzie index.php WordPressa),
 *    tak żeby był dostępny pod https://www.officeinfluencers.pl/formularz.php
 * 2. W stałej NADAWCA ustaw istniejącą skrzynkę na domenie officeinfluencers.pl.
 *    Adres nadawcy MUSI być na tej domenie, inaczej SPF i DMARC odrzucą wiadomość
 *    i maile będą lądować w spamie. Adres osoby zgłaszającej idzie w Reply-To,
 *    więc odpowiadasz jej zwykłym „Odpowiedz”.
 * 3. Jeśli Zenbox blokuje funkcję mail(), przełącz się na SMTP tej samej skrzynki
 *    (dane logowania znajdziesz w panelu, w sekcji Poczta).
 *
 * Adresy odbiorców są tu na sztywno. Nie bierzemy ich z formularza, bo inaczej
 * dowolna osoba mogłaby użyć tego skryptu do rozsyłania poczty na cudze adresy.
 */

const ODBIORCY = [
    'zgloszenie' => 'office@officeinfluencers.pl',
    'oferta'     => 'agnieszka.korach@arkconsulting.com.pl',
];

const TEMATY = [
    'zgloszenie' => 'Zgłoszenie na szkolenie APZ',
    'oferta'     => 'Zapytanie dot. szkolenia APZ',
];

// Skrzynka na domenie officeinfluencers.pl, z której wychodzi wiadomość.
const NADAWCA      = 'strona@officeinfluencers.pl';
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

const MAX_DLUGOSC = 2000;

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

function odpowiedz(int $kod, string $komunikat): void
{
    http_response_code($kod);
    echo json_encode(
        ['ok' => $kod === 200, 'komunikat' => $komunikat],
        JSON_UNESCAPED_UNICODE
    );
    exit;
}

/** Usuwa znaki, którymi dałoby się dopisać własne nagłówki wiadomości. */
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
    return trim(mb_substr($surowa, 0, MAX_DLUGOSC));
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    odpowiedz(405, 'Dozwolona jest tylko metoda POST.');
}

// Pole-pułapka: wypełniają je boty, ludzie go nie widzą.
if (wartosc('www') !== '') {
    odpowiedz(200, 'Dziękujemy.');
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

odpowiedz(200, 'Dziękujemy, wiadomość została wysłana.');
