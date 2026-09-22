<?php
declare(strict_types=1);

/**
 * Obsługa formularza zgłoszeniowego – OFFICE MANAGER AI OPERATIONS
 * Hosting: Zenbox (PHP + funkcja mail()).
 *
 * Plik leży obok index.html. Formularz wysyła tu POST-a; skrypt waliduje dane,
 * zapisuje zgłoszenie do pliku CSV i wysyła maila na ODBIORCA.
 *
 * ── KONFIGURACJA ──────────────────────────────────────────────────────────
 * NADAWCA musi być adresem w domenie, z której działa strona. Jeśli wpiszesz
 * tu adres zgłaszającego, poczta odbiorcy odrzuci maila albo wrzuci go do
 * spamu (SPF/DKIM). Adres zgłaszającego trafia do nagłówka Reply-To, więc
 * odpowiadasz na maila normalnie, jednym kliknięciem.
 *
 * KATALOG_DANYCH zawiera dane osobowe. Skrypt sam zakłada w nim .htaccess
 * blokujący dostęp z zewnątrz, ale NAJBEZPIECZNIEJ jest trzymać go poza
 * katalogiem publicznym, np.:
 *     const KATALOG_DANYCH = __DIR__ . '/../../dane-zgloszenia';
 * (czyli obok public_html, a nie w środku). Zobacz WDROZENIE.md.
 */
const ODBIORCA       = 'office@officeinfluencers.pl';
const NADAWCA        = 'formularz@officeinfluencers.pl';
const NAZWA_NADAWCY  = 'Formularz Office Influencers';
const PROGRAM        = 'OFFICE MANAGER AI OPERATIONS';
const MAX_OSOB       = 10;
const STRONA         = 'index.html';

const KATALOG_DANYCH = __DIR__ . '/dane';
const PLIK_CSV       = 'zgloszenia.csv';
const SEPARATOR_CSV  = ';'; // średnik – polski Excel otwiera taki plik bez importu

// ──────────────────────────────────────────────────────────────────────────

/* sprawdz.php dociąga stąd samą konfigurację i nie uruchamia obsługi formularza */
if (defined('TYLKO_KONFIGURACJA')) {
    return;
}

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

/**
 * Dopisuje zgłoszenie do pliku CSV.
 * Zwraca true, jeśli wiersz trafił na dysk.
 */
function zapiszCsv(array $wiersz, array $naglowki): bool
{
    $katalog = KATALOG_DANYCH;

    if (!is_dir($katalog) && !@mkdir($katalog, 0750, true) && !is_dir($katalog)) {
        error_log('[ai-office-manager] nie mogę utworzyć katalogu: ' . $katalog);
        return false;
    }

    // Gdyby katalog leżał w części publicznej – odcinamy dostęp z przeglądarki.
    $ochrona = $katalog . '/.htaccess';
    if (!file_exists($ochrona)) {
        @file_put_contents($ochrona, implode("\n", [
            '# Katalog z danymi osobowymi – brak dostępu z zewnątrz.',
            '<IfModule mod_authz_core.c>',
            '  Require all denied',
            '</IfModule>',
            '<IfModule !mod_authz_core.c>',
            '  Order allow,deny',
            '  Deny from all',
            '</IfModule>',
            '',
        ]));
    }
    if (!file_exists($katalog . '/index.html')) {
        @file_put_contents($katalog . '/index.html', '');
    }

    $plik = $katalog . '/' . PLIK_CSV;
    $nowy = !file_exists($plik);

    $uchwyt = @fopen($plik, 'a');
    if ($uchwyt === false) {
        error_log('[ai-office-manager] nie mogę otworzyć pliku CSV: ' . $plik);
        return false;
    }

    if (!flock($uchwyt, LOCK_EX)) {
        fclose($uchwyt);
        error_log('[ai-office-manager] nie mogę zablokować pliku CSV');
        return false;
    }

    if ($nowy) {
        // BOM – bez niego Excel psuje polskie znaki
        fwrite($uchwyt, "\xEF\xBB\xBF");
        fputcsv($uchwyt, $naglowki, SEPARATOR_CSV, '"', '');
    }
    fputcsv($uchwyt, $wiersz, SEPARATOR_CSV, '"', '');

    fflush($uchwyt);
    flock($uchwyt, LOCK_UN);
    fclose($uchwyt);

    @chmod($plik, 0640);

    return true;
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
$imie       = pole('imie_nazwisko');
$stanowisko = pole('stanowisko');
$firma      = pole('firma');
$email      = pole('email');
$telefon    = pole('telefon');
$osoby      = pole('liczba_osob');
$wiadomosc  = str_replace("\r\n", "\n", pole('wiadomosc'));
$zgoda      = pole('zgoda') !== '';

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

// ── 4. Zapis do CSV (najpierw, żeby zgłoszenie nie przepadło) ─────────────
$data = date('Y-m-d H:i:s');
$ip   = $_SERVER['REMOTE_ADDR'] ?? 'nieznane';

$zapisano = zapiszCsv(
    [$data, $imie, $stanowisko, $firma, $email, $telefon, $liczbaOsob, $wiadomosc, 'TAK', PROGRAM, $ip],
    ['Data zgłoszenia', 'Imię i nazwisko', 'Stanowisko', 'Firma', 'E-mail', 'Telefon',
     'Liczba osób', 'Wiadomość', 'Zgoda', 'Program', 'IP']
);

// ── 5. Mail ───────────────────────────────────────────────────────────────
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
    'Data zgłoszenia: ' . $data,
    'IP: ' . $ip,
    'Zapis w pliku ' . PLIK_CSV . ': ' . ($zapisano ? 'tak' : 'NIE – sprawdź uprawnienia katalogu'),
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

// ── 6. Odpowiedź ──────────────────────────────────────────────────────────
// Zgłoszenie uznajemy za przyjęte, jeśli zadziałał przynajmniej jeden kanał.
if (!$wyslano) {
    error_log('[ai-office-manager] mail() nie zadziałał dla: ' . $email
        . ' | zapis CSV: ' . ($zapisano ? 'ok' : 'NIE'));
}
if (!$zapisano) {
    error_log('[ai-office-manager] zapis do CSV nie zadziałał dla: ' . $email
        . ' | mail: ' . ($wyslano ? 'ok' : 'NIE'));
}

if (!$wyslano && !$zapisano) {
    odpowiedz(
        false,
        'Nie udało się wysłać zgłoszenia. Napisz bezpośrednio na ' . ODBIORCA . '.',
        500
    );
}

odpowiedz(true, 'Zgłoszenie przyjęte.');
