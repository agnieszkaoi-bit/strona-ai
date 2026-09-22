<?php
declare(strict_types=1);

/**
 * Obsługa formularza zgłoszeniowego – OFFICE MANAGER AI OPERATIONS
 * Hosting: Zenbox (PHP + funkcja mail()).
 *
 * Plik leży obok index.html. Formularz wysyła tu POST-a; skrypt waliduje dane,
 * składa wiadomość i wysyła ją na ODBIORCA.
 *
 * ── KONFIGURACJA ──────────────────────────────────────────────────────────
 * NADAWCA musi być adresem w domenie, z której działa strona. Jeśli wpiszesz
 * tu adres zgłaszającego, poczta odbiorcy odrzuci maila albo wrzuci go do
 * spamu (SPF/DKIM). Adres zgłaszającego trafia do nagłówka Reply-To, więc
 * odpowiadasz na maila normalnie, jednym kliknięciem.
 */
const ODBIORCA       = 'office@officeinfluencers.pl';
const NADAWCA        = 'formularz@officeinfluencers.pl';
const NAZWA_NADAWCY  = 'Formularz Office Influencers';
const PROGRAM        = 'OFFICE MANAGER AI OPERATIONS';
const MAX_OSOB       = 10;
const STRONA         = 'index.html';

// ──────────────────────────────────────────────────────────────────────────

/** Usuwa znaki nowej linii – zabezpieczenie przed wstrzyknięciem nagłówków. */
function bezNowychLinii(string $v): string
{
    return trim(str_replace(["\r", "\n", "%0a", "%0d"], ' ', $v));
}

/** Koduje nagłówek z polskimi znakami zgodnie z RFC 2047. */
function naglowekUtf8(string $v): string
{
    return '=?UTF-8?B?' . base64_encode($v) . '?=';
}

function pole(string $nazwa): string
{
    $v = $_POST[$nazwa] ?? '';
    return is_string($v) ? trim($v) : '';
}

/** Odpowiada JSON-em (AJAX) albo przekierowuje z powrotem na stronę. */
function odpowiedz(bool $ok, string $komunikat, int $kod = 200): never
{
    $ajax = isset($_SERVER['HTTP_X_REQUESTED_WITH'])
        && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

    if ($ajax) {
        http_response_code($kod);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['ok' => $ok, 'komunikat' => $komunikat], JSON_UNESCAPED_UNICODE);
    } else {
        header('Location: ' . STRONA . ($ok ? '?wyslano=1' : '?blad=1') . '#zapisy', true, 303);
    }
    exit;
}

// ── 1. Tylko POST ─────────────────────────────────────────────────────────
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    odpowiedz(false, 'Nieprawidłowe żądanie.', 405);
}

// ── 2. Pułapka na boty: pole ukryte w CSS, człowiek go nie wypełni ────────
if (pole('strona_www') !== '') {
    odpowiedz(true, 'Dziękujemy.'); // cicho udajemy sukces
}

// ── 3. Walidacja ──────────────────────────────────────────────────────────
$imie      = pole('imie_nazwisko');
$stanowisko = pole('stanowisko');
$firma     = pole('firma');
$email     = pole('email');
$telefon   = pole('telefon');
$osoby     = pole('liczba_osob');
$wiadomosc = pole('wiadomosc');
$zgoda     = pole('zgoda') !== '';

$bledy = [];
if ($imie === '')       { $bledy[] = 'imię i nazwisko'; }
if ($stanowisko === '') { $bledy[] = 'stanowisko'; }
if ($firma === '')      { $bledy[] = 'firma'; }
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $bledy[] = 'poprawny e-mail'; }
if (!$zgoda)            { $bledy[] = 'zgoda na przetwarzanie danych'; }

$liczbaOsob = filter_var($osoby, FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1, 'max_range' => MAX_OSOB],
]);
if ($liczbaOsob === false) {
    $bledy[] = 'liczba osób od 1 do ' . MAX_OSOB;
}

if ($bledy !== []) {
    odpowiedz(false, 'Uzupełnij: ' . implode(', ', $bledy) . '.', 422);
}

// ── 4. Wiadomość ──────────────────────────────────────────────────────────
$temat = 'Zgłoszenie: ' . PROGRAM . ($firma !== '' ? ' – ' . $firma : '');

$tresc = implode("\n", [
    'Zgłoszenie na program: ' . PROGRAM,
    '(4 tygodnie online, 4 × 2h LIVE + 1h z prawnikiem)',
    '',
    'Imię i nazwisko: ' . $imie,
    'Stanowisko: ' . $stanowisko,
    'Firma: ' . $firma,
    'E-mail: ' . $email,
    'Telefon: ' . ($telefon !== '' ? $telefon : 'nie podano'),
    'Liczba zgłaszanych osób: ' . $liczbaOsob,
    '',
    'Wiadomość:',
    $wiadomosc !== '' ? $wiadomosc : '(brak)',
    '',
    str_repeat('-', 40),
    'Zgoda na kontakt i przetwarzanie danych w celu obsługi zgłoszenia: TAK',
    'Data zgłoszenia: ' . date('Y-m-d H:i:s'),
    'IP: ' . ($_SERVER['REMOTE_ADDR'] ?? 'nieznane'),
]);

$naglowki = implode("\r\n", [
    'From: ' . naglowekUtf8(NAZWA_NADAWCY) . ' <' . NADAWCA . '>',
    'Reply-To: ' . bezNowychLinii($email),
    'Content-Type: text/plain; charset=UTF-8',
    'Content-Transfer-Encoding: 8bit',
    'MIME-Version: 1.0',
    'X-Mailer: PHP/' . PHP_VERSION,
]);

$wyslano = @mail(
    ODBIORCA,
    naglowekUtf8(bezNowychLinii($temat)),
    $tresc,
    $naglowki,
    '-f' . NADAWCA
);

if (!$wyslano) {
    error_log('[ai-office-manager] mail() nie zadziałał dla: ' . $email);
    odpowiedz(
        false,
        'Nie udało się wysłać zgłoszenia. Napisz bezpośrednio na ' . ODBIORCA . '.',
        500
    );
}

odpowiedz(true, 'Zgłoszenie wysłane.');
