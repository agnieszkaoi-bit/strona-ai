<?php
declare(strict_types=1);

@ini_set('display_errors', '0');
@ini_set('display_startup_errors', '0');
@ini_set('log_errors', '1');
error_reporting(E_ALL);

$tenPlik = realpath(__FILE__);
$wywolany = realpath((string)($_SERVER['SCRIPT_FILENAME'] ?? ''));
if (is_string($tenPlik) && is_string($wywolany) && $tenPlik === $wywolany) {
    http_response_code(404);
    exit;
}

const DOZWOLONE_HOSTY = [
    'officeinfluencers.pl',
    'www.officeinfluencers.pl',
];

const MAX_POL     = 30;
const MAX_ZADANIA = 20000;

function naglowkiBezpieczenstwa(): void
{
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-Robots-Tag: noindex, nofollow');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Cache-Control: no-store');
    header('Content-Security-Policy: default-src \'none\'; frame-ancestors \'none\'');
    header_remove('X-Powered-By');
}

function hostZAdresu(string $adres): string
{
    $host = parse_url(trim($adres), PHP_URL_HOST);
    return is_string($host) ? strtolower($host) : '';
}

function sprawdzPochodzenie(bool $wymagany, callable $odmowa): void
{
    $origin  = hostZAdresu((string)($_SERVER['HTTP_ORIGIN'] ?? ''));
    $referer = hostZAdresu((string)($_SERVER['HTTP_REFERER'] ?? ''));
    $host    = $origin !== '' ? $origin : $referer;

    if ($host === '') {
        if ($wymagany) {
            $odmowa();
        }
        return;
    }
    if (!in_array($host, DOZWOLONE_HOSTY, true)) {
        $odmowa();
    }
}

/*
 * Nagłówki Sec-Fetch-* ustawia sama przeglądarka i strona nie może ich
 * podmienić. Mówią, skąd wyszło żądanie. Cudza strona podszywająca się pod
 * formularz dostanie tu "cross-site" i odpadnie. Starsze przeglądarki tych
 * nagłówków nie wysyłają, więc ich brak przepuszczamy.
 */
function sprawdzMetadanePobrania(callable $odmowa): void
{
    $skad = strtolower(trim((string)($_SERVER['HTTP_SEC_FETCH_SITE'] ?? '')));
    if ($skad !== '' && $skad !== 'same-origin' && $skad !== 'none') {
        $odmowa();
    }
    $tryb = strtolower(trim((string)($_SERVER['HTTP_SEC_FETCH_MODE'] ?? '')));
    if ($tryb === 'navigate' && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
        $odmowa();
    }
}

function rozmiarZadania(): int
{
    return max(0, (int)($_SERVER['CONTENT_LENGTH'] ?? 0));
}

function odciskIp(): string
{
    $ip = (string)($_SERVER['REMOTE_ADDR'] ?? 'nieznany');
    return hash('sha256', $ip);
}

function sprzatnijLimity(string $katalog, int $okno): void
{
    if (random_int(1, 50) !== 1) {
        return;
    }
    $granica = time() - max($okno, 3600) * 2;
    foreach (glob($katalog . '/*.txt') ?: [] as $stary) {
        if (@filemtime($stary) < $granica) {
            @unlink($stary);
        }
    }
}

function limitZadan(string $nazwa, int $ile, int $okno, callable $odmowa, ?string $klucz = null): void
{
    $katalog = sys_get_temp_dir() . '/oi-limity';
    if (!is_dir($katalog) && !@mkdir($katalog, 0700, true) && !is_dir($katalog)) {
        return;
    }
    sprzatnijLimity($katalog, $okno);

    $plik = $katalog . '/' . $nazwa . '-' . ($klucz ?? odciskIp()) . '.txt';
    $uchwyt = @fopen($plik, 'c+');
    if ($uchwyt === false) {
        return;
    }

    $przekroczony = false;
    if (flock($uchwyt, LOCK_EX)) {
        $teraz  = time();
        $tresc  = (string)stream_get_contents($uchwyt);
        $czasy  = array_filter(
            array_map('intval', array_filter(explode(',', $tresc), 'strlen')),
            static fn(int $t): bool => $t > $teraz - $okno
        );

        if (count($czasy) >= $ile) {
            $przekroczony = true;
        } else {
            $czasy[] = $teraz;
        }

        $czasy = array_slice($czasy, -$ile);
        ftruncate($uchwyt, 0);
        rewind($uchwyt);
        fwrite($uchwyt, implode(',', $czasy));
        fflush($uchwyt);
        flock($uchwyt, LOCK_UN);
    }
    fclose($uchwyt);

    if ($przekroczony) {
        $odmowa();
    }
}

const WAGI_NIP = [6, 5, 7, 2, 3, 4, 5, 6, 7];

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

function poprawneUtf8(string $wartosc): bool
{
    return $wartosc === '' || (bool)preg_match('//u', $wartosc);
}

/*
 * Usuwa znaki, których nie widać na ekranie: sterujące, znaczniki kierunku
 * pisma, spacje zerowej szerokości i blok Unicode Tags. W treści wpisanej
 * przez człowieka nie mają czego szukać, a służą do ukrywania tekstu, który
 * zobaczy dopiero program czytający wiadomość.
 */
function bezZnakowSterujacych(string $wartosc): string
{
    $czysta = preg_replace(
        '/[\x{0000}-\x{0009}\x{000B}-\x{001F}\x{007F}\x{2028}\x{2029}\p{Cf}]/u',
        '',
        $wartosc
    );
    return is_string($czysta) ? $czysta : '';
}

/*
 * Znaczniki, którymi programy językowe oddzielają polecenia od danych.
 * W zgłoszeniu na szkolenie nie występują, więc wycinamy je w całości.
 */
const ZNACZNIKI_MODELI = [
    '/<\|[^|>\r\n]{0,60}\|>/u',
    '/\[\/?INST\]/i',
    '/<<\s*\/?\s*SYS\s*>>/i',
    '/<\/?\s*(system|assistant|user|instructions?|prompt)\s*>/i',
    '/`{3,}/',
    '/~{3,}/',
];

function bezZnacznikow(string $tekst): string
{
    foreach (ZNACZNIKI_MODELI as $wzor) {
        $tekst = (string)preg_replace($wzor, ' ', $tekst);
    }
    return $tekst;
}

/*
 * Rozpoznaje tekst napisany nie do człowieka, tylko do programu czytającego
 * wiadomość. Nie odrzucamy takiego zgłoszenia, bo zdanie w rodzaju "proszę
 * zignorować poprzednie zgłoszenie" jest zupełnie zwyczajne. Dopisujemy
 * ostrzeżenie na górze wiadomości i zostawiamy ocenę człowiekowi.
 */
const WZORY_POLECEN = [
    '/\b(ignor\w*|disregard|forget|pomi[nń]\w*|zignoruj|zapomnij)\b[^.!?\n]{0,60}\b(instruction\w*|prompt\w*|rules?|polece\w+|instrukcj\w+|zasad\w+|powy[żz]sz\w+|poprzedni\w*|wszystk\w+)/iu',
    '/\b(system|developer|assistant)\b[^.!?\n]{0,20}\b(prompt|message|instruction\w*)/iu',
    '/\b(you\s+are\s+now|from\s+now\s+on|act\s+as|pretend\s+to\s+be)\b/iu',
    '/\b(jeste[śs]\s+teraz|od\s+teraz\s+jeste[śs]|zachowuj\s+si[ęe]\s+jak|wciel\s+si[ęe]\s+w)\b/iu',
    '/\b(reveal|print|repeat|show|output|ujawnij|wypisz|poka[żz]|powtórz)\b[^.!?\n]{0,40}\b(prompt|instruction\w*|system|api[\s_-]?key|token|secret|has[łl]\w*|klucz\w*)/iu',
    '/\b(new|updated|nowe|aktualne)\s+(instruction\w*|task|rules?|instrukcj\w+|polecen\w+|zasad\w+)\b/iu',
    '/^\s*(#{1,6}\s*)?(system|assistant|user|developer)\s*[:：]/imu',
];

function wygladaNaPolecenie(string $tekst): bool
{
    foreach (WZORY_POLECEN as $wzor) {
        if (preg_match($wzor, $tekst)) {
            return true;
        }
    }
    return false;
}

/*
 * Każdy wiersz dostaje z przodu kreskę. Dzięki temu żaden wiersz wpisany
 * przez odwiedzającego nie wygląda na nagłówek, ramkę ani nową sekcję
 * wiadomości, choćby ktoś bardzo się starał.
 */
function cytujJakoDane(string $tekst): string
{
    $wiersze = preg_split('/\n/', bezZnacznikow($tekst)) ?: [];
    $wynik = [];
    foreach ($wiersze as $wiersz) {
        $wynik[] = rtrim('| ' . rtrim($wiersz));
    }
    return implode("\n", $wynik);
}
