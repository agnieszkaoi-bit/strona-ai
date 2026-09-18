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

/*
 * MAILERLITE
 * Token: MailerLite → Integrations → API → Generate new token (uprawnienie do subskrybentów).
 * ID grupy: MailerLite → Subscribers → Groups → wejdź w grupę, numer jest w adresie strony.
 * Puste pole grupy = zapis do ogólnej listy.
 *
 * Token zostaje tutaj, po stronie serwera. W kodzie strony nie może się pojawić,
 * bo każdy odwiedzający mógłby go odczytać i wykorzystać do Twojego konta.
 *
 * Na listę trafia wyłącznie osoba, która zaznaczyła zgodę marketingową.
 * Zgoda RODO dotyczy obsługi zgłoszenia i do zapisu na listę nie wystarcza.
 */
const MAILERLITE_TOKEN = '';
const MAILERLITE_GRUPA = '';

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

/** Rozbija „Anna Kowalska” na imię i nazwisko dla pól MailerLite. */
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

/*
 * Zapis jest dodatkiem do zgłoszenia, nie warunkiem. Gdy MailerLite nie
 * odpowie, zgłoszenie i tak jest przyjęte, a ślad trafia do logu serwera.
 * Statusu subskrypcji nie narzucamy, żeby zadziałało ustawienie double opt-in
 * z Twojego konta.
 */
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

// Pole-pułapka: wypełniają je boty, ludzie go nie widzą.
if (wartosc('www') !== '') {
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

if ($typ === 'zgloszenie' && wartosc('zgoda_marketing') !== '') {
    doMailerLite($email, wartosc('imie_nazwisko'));
}

odpowiedz(200, 'Dziękuję, wiadomość została wysłana.');
