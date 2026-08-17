<?php
/**
 * ARK Consulting — obsługa formularza kontaktowego (kontakt.html)
 * Prosty, samodzielny skrypt PHP do wysyłki e-maila przez funkcję mail()
 * dostępną standardowo na hostingu Zenbox. Nie wymaga bazy danych ani
 * dodatkowych bibliotek.
 *
 * ZABEZPIECZENIA ZASTOSOWANE W TYM PLIKU:
 * - tylko metoda POST jest akceptowana,
 * - honeypot (pole "website" niewidoczne dla ludzi, wypełniane przez boty),
 * - opcjonalny test czasowy (formularz wypełniony błyskawicznie = bot),
 * - twarde limity długości pól (ochrona przed przeciążeniem / mail bombing),
 * - usuwanie znaków CR/LF ze wszystkich pól nagłówkowych (ochrona przed
 *   header injection / wstrzykiwaniem dodatkowych nagłówków e-mail, w tym
 *   BCC do wysyłki spamu przez Twój formularz),
 * - "Temat" ograniczony do zamkniętej listy dozwolonych wartości,
 * - stały adres "From" (nadawcą zawsze jest Twoja domena, nie dane od
 *   użytkownika) — obniża ryzyko trafienia do SPAM-u i podszywania się,
 * - błędy PHP nie są wyświetlane użytkownikowi (brak wycieku ścieżek
 *   serwera w razie awarii).
 *
 * WAŻNE PRZED URUCHOMIENIEM NA ZENBOX:
 * 1) Zmień adres w $odbiorca poniżej, jeśli ma być inny niż domyślny.
 * 2) Upewnij się, że w panelu Zenbox skrzynka $odbiorca istnieje i że
 *    wysyłka z PHP (funkcja mail()) jest włączona dla domeny.
 * 3) Jeśli maile trafiają do SPAM-u, skonfiguruj rekordy SPF/DKIM dla
 *    domeny arkconsulting.com.pl w panelu Zenbox (Strefa DNS).
 */

// Błędy PHP wyłącznie do logów serwera, nigdy na ekran użytkownika
ini_set('display_errors', '0');
error_reporting(E_ALL);

header('Content-Type: text/html; charset=UTF-8');
header('X-Content-Type-Options: nosniff');

$odbiorca     = 'szkolenia@arkconsulting.com.pl';
$stronaBledu  = '/kontakt/?blad=1';
$stronaSukcesu = '/dziekujemy/';

$dozwoloneTematy = [
    'Szkolenie z AI dla pracowników',
    'Szkolenie dla managera',
    'Szkolenie dla asystentki (Akademia Asystentek)',
    'Odporność psychiczna zespołu',
    'Inne / nie wiem jeszcze',
];

// --- Tylko POST ---
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Location: ' . $stronaBledu, true, 303);
    exit;
}

// --- Honeypot antyspamowy — bot wypełnia niewidoczne pole ---
if (!empty($_POST['website'])) {
    header('Location: ' . $stronaSukcesu, true, 303); // udajemy sukces, nic nie wysyłamy
    exit;
}

// --- Test czasowy (opcjonalny, ustawiany przez JS w main.js) ---
// Jeśli pole "ts" jest obecne i formularz wysłano szybciej niż w 3 sekundy
// od załadowania strony, traktujemy to jako bota. Brak pola "ts" (np. gdy
// JavaScript jest wyłączony) nie blokuje wysyłki — nie karzemy realnych
// użytkowników za brak JS.
if (!empty($_POST['ts']) && ctype_digit((string) $_POST['ts'])) {
    $wyslanoPoMs = (int) round(microtime(true) * 1000) - (int) $_POST['ts'];
    if ($wyslanoPoMs >= 0 && $wyslanoPoMs < 3000) {
        header('Location: ' . $stronaSukcesu, true, 303);
        exit;
    }
}

/**
 * Usuwa znaki sterujące (w tym CR/LF — ochrona przed header injection),
 * przycina białe znaki i ogranicza długość pola.
 */
function oczysc(string $wartosc, int $maxDlugosc = 200): string
{
    $wartosc = trim($wartosc);
    // Usuń wszystkie znaki sterujące ASCII (0x00–0x1F, 0x7F), w tym CR i LF
    $wartosc = preg_replace('/[\x00-\x1F\x7F]+/u', ' ', $wartosc) ?? '';
    $wartosc = trim($wartosc);
    if (function_exists('mb_substr')) {
        $wartosc = mb_substr($wartosc, 0, $maxDlugosc);
    } else {
        $wartosc = substr($wartosc, 0, $maxDlugosc);
    }
    return $wartosc;
}

$imie    = oczysc($_POST['imie'] ?? '', 120);
$firma   = oczysc($_POST['firma'] ?? '', 150);
$email   = oczysc($_POST['email'] ?? '', 190);
$telefon = oczysc($_POST['telefon'] ?? '', 40);
$temat   = oczysc($_POST['temat'] ?? '', 120);
$zgoda   = isset($_POST['zgoda']);

// Wiadomość: bez CR/LF-stripping (wieloliniowa), ale z limitem długości i
// bez znaków sterujących poza standardowymi łamaniami linii
$wiadomoscSurowa = (string) ($_POST['wiadomosc'] ?? '');
$wiadomoscSurowa = trim($wiadomoscSurowa);
$wiadomoscSurowa = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/u', '', $wiadomoscSurowa) ?? '';
$wiadomosc = function_exists('mb_substr') ? mb_substr($wiadomoscSurowa, 0, 5000) : substr($wiadomoscSurowa, 0, 5000);

// --- Walidacja pól wymaganych ---
if ($imie === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || !$zgoda) {
    header('Location: ' . $stronaBledu, true, 303);
    exit;
}

// --- Temat: tylko wartości z zamkniętej listy ---
if (!in_array($temat, $dozwoloneTematy, true)) {
    $temat = 'Inne / nie wiem jeszcze';
}

$tytul = '[Formularz kontaktowy] ' . $temat . ' — ' . $imie;

$tresc  = "Nowe zapytanie ze strony arkconsulting.com.pl\n\n";
$tresc .= "Imię i nazwisko: {$imie}\n";
$tresc .= "Firma / instytucja: {$firma}\n";
$tresc .= "E-mail: {$email}\n";
$tresc .= "Telefon: {$telefon}\n";
$tresc .= "Temat: {$temat}\n\n";
$tresc .= "Wiadomość:\n{$wiadomosc}\n";

// Nadawca ("From") jest zawsze stały i należy do Twojej domeny — dane od
// użytkownika trafiają wyłącznie do "Reply-To". Zapobiega to podszywaniu
// się pod cudze adresy i obniża ryzyko trafienia do SPAM-u.
$naglowki  = "From: ARK Consulting — formularz <formularz@arkconsulting.com.pl>\r\n";
$naglowki .= "Reply-To: {$email}\r\n";
$naglowki .= "Content-Type: text/plain; charset=UTF-8\r\n";
$naglowki .= "Content-Transfer-Encoding: 8bit\r\n";
$naglowki .= "X-Mailer: ARK-Consulting-Formularz/1.1\r\n";

$wyslano = @mail($odbiorca, $tytul, $tresc, $naglowki);

header('Location: ' . ($wyslano ? $stronaSukcesu : $stronaBledu), true, 303);
exit;
