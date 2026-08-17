<?php
/**
 * ARK Consulting — obsługa formularza kontaktowego (kontakt.html)
 * Prosty, samodzielny skrypt PHP do wysyłki e-maila przez funkcję mail()
 * dostępną standardowo na hostingu Zenbox. Nie wymaga bazy danych ani
 * dodatkowych bibliotek.
 *
 * WAŻNE PRZED URUCHOMIENIEM NA ZENBOX:
 * 1) Zmień adres w $odbiorca poniżej, jeśli ma być inny niż domyślny.
 * 2) Upewnij się, że w panelu Zenbox skrzynka $odbiorca istnieje i że
 *    wysyłka z PHP (funkcja mail()) jest włączona dla domeny — Zenbox
 *    wspiera to standardowo dla własnych domen w hostingu.
 * 3) Jeśli maile trafiają do SPAM-u, skonfiguruj rekordy SPF/DKIM dla
 *    domeny arkconsulting.com.pl w panelu Zenbox (Strefa DNS).
 */

header('Content-Type: text/html; charset=UTF-8');

$odbiorca = 'szkolenia@arkconsulting.com.pl';
$stronaBledu = '/kontakt.html?blad=1';
$stronaSukcesu = '/dziekujemy.html';

// Akceptujemy tylko żądania POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . $stronaBledu);
    exit;
}

// Honeypot antyspamowy — jeśli wypełnione, to bot: udajemy sukces i kończymy
if (!empty($_POST['website'])) {
    header('Location: ' . $stronaSukcesu);
    exit;
}

function oczysc($wartosc) {
    $wartosc = trim($wartosc ?? '');
    $wartosc = str_replace(["\r", "\n"], ' ', $wartosc); // ochrona przed header injection
    return htmlspecialchars($wartosc, ENT_QUOTES, 'UTF-8');
}

$imie      = oczysc($_POST['imie'] ?? '');
$firma     = oczysc($_POST['firma'] ?? '');
$email     = oczysc($_POST['email'] ?? '');
$telefon   = oczysc($_POST['telefon'] ?? '');
$temat     = oczysc($_POST['temat'] ?? '');
$wiadomosc = trim($_POST['wiadomosc'] ?? '');
$zgoda     = isset($_POST['zgoda']);

// Walidacja pól wymaganych
if ($imie === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || !$zgoda) {
    header('Location: ' . $stronaBledu);
    exit;
}

$wiadomoscOczyszczona = htmlspecialchars($wiadomosc, ENT_QUOTES, 'UTF-8');

$tytul = '[Formularz kontaktowy] ' . ($temat !== '' ? $temat : 'Nowe zapytanie') . ' — ' . $imie;

$tresc  = "Nowe zapytanie ze strony arkconsulting.com.pl\n\n";
$tresc .= "Imię i nazwisko: {$imie}\n";
$tresc .= "Firma / instytucja: {$firma}\n";
$tresc .= "E-mail: {$email}\n";
$tresc .= "Telefon: {$telefon}\n";
$tresc .= "Temat: {$temat}\n\n";
$tresc .= "Wiadomość:\n{$wiadomoscOczyszczona}\n";

$naglowki   = "From: ARK Consulting — formularz <formularz@arkconsulting.com.pl>\r\n";
$naglowki  .= "Reply-To: {$email}\r\n";
$naglowki  .= "Content-Type: text/plain; charset=UTF-8\r\n";

$wyslano = @mail($odbiorca, $tytul, $tresc, $naglowki);

if ($wyslano) {
    header('Location: ' . $stronaSukcesu);
} else {
    header('Location: ' . $stronaBledu);
}
exit;
