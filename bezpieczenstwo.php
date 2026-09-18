<?php
declare(strict_types=1);

/*
 * Wspólne zabezpieczenia dla formularz.php i nip.php.
 *
 * WGRANIE NA ZENBOX
 * Ten plik musi leżeć w tym samym katalogu co formularz.php i nip.php.
 * Sam z siebie nic nie robi i nie ma nic do pokazania w przeglądarce –
 * jest dołączany przez tamte dwa skrypty.
 *
 * Co tu jest i po co:
 *   1. Wyciszenie komunikatów o błędach. Domyślnie PHP potrafi wypisać
 *      na stronie ścieżkę do pliku albo fragment kodu. To gotowa mapa
 *      dla kogoś, kto szuka dziury. Błędy nadal trafiają do logu serwera.
 *   2. Sprawdzenie, skąd przyszło żądanie. Przyjmujemy tylko to, co
 *      wysłała Twoja strona. Formularz podstawiony na cudzej domenie
 *      dostanie odmowę.
 *   3. Limit liczby żądań z jednego adresu IP. Bez niego ktoś mógłby
 *      w pętli wysyłać zgłoszenia i zapchać Ci skrzynkę albo odpytywać
 *      rejestr NIP tak długo, aż Ministerstwo Finansów zablokuje serwer.
 */

// --- 1. Błędy do logu, nie na ekran ---------------------------------------
@ini_set('display_errors', '0');
@ini_set('display_startup_errors', '0');
@ini_set('log_errors', '1');
error_reporting(E_ALL);

// Domeny, z których wolno korzystać z tych skryptów.
const DOZWOLONE_HOSTY = [
    'officeinfluencers.pl',
    'www.officeinfluencers.pl',
];

/** Nagłówki, które mówią przeglądarce, jak ostrożnie obchodzić się z odpowiedzią. */
function naglowkiBezpieczenstwa(): void
{
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Cache-Control: no-store');
    header_remove('X-Powered-By');
}

/** Wyciąga samą nazwę domeny z adresu w nagłówku Origin albo Referer. */
function hostZAdresu(string $adres): string
{
    $host = parse_url(trim($adres), PHP_URL_HOST);
    return is_string($host) ? strtolower($host) : '';
}

/*
 * Żądanie ma pochodzić z naszej strony.
 *
 * Przy wysyłce formularza przeglądarka zawsze dokłada nagłówek Origin,
 * więc tam wymagamy go wprost ($wymagany = true). Przy zwykłym pobraniu
 * danych po NIP przeglądarka Origin pomija, zostaje sam Referer – wtedy
 * sprawdzamy go, jeśli jest, a brak nagłówka puszczamy dalej, bo i tak
 * chroni nas limit liczby żądań.
 */
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

/** Adres IP osoby wysyłającej żądanie, w postaci skróconej do nazwy pliku. */
function odciskIp(): string
{
    $ip = (string)($_SERVER['REMOTE_ADDR'] ?? 'nieznany');
    return hash('sha256', $ip);
}

/*
 * Prosty licznik w plikach: dla każdego adresu IP trzymamy znaczniki czasu
 * ostatnich żądań i odrzucamy te ponad limit. Pliki lądują w katalogu
 * tymczasowym serwera, poza katalogiem strony, więc nikt ich nie pobierze.
 * Blokada flock sprawia, że dwa żądania naraz nie nadpiszą sobie licznika.
 */
function limitZadan(string $nazwa, int $ile, int $okno, callable $odmowa): void
{
    $katalog = sys_get_temp_dir() . '/oi-limity';
    if (!is_dir($katalog) && !@mkdir($katalog, 0700, true) && !is_dir($katalog)) {
        return; // brak miejsca na licznik nie może blokować zgłoszeń
    }

    $plik = $katalog . '/' . $nazwa . '-' . odciskIp() . '.txt';
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

        // zapisujemy najwyżej tyle znaczników, ile wynosi limit
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

/*
 * Usuwa znaki sterujące, którymi da się zaciemnić treść wiadomości albo
 * podszyć pod nagłówek poczty. Zwykły znak nowej linii zostaje, bo pole
 * „Uwagi” bywa kilkuliniowe – ale powrót karetki już nie, żeby w wiadomości
 * nie dało się udawać osobnej linii nagłówka.
 */
function bezZnakowSterujacych(string $wartosc): string
{
    $czysta = preg_replace('/[\x00-\x09\x0B-\x1F\x7F]/u', '', $wartosc);
    return is_string($czysta) ? $czysta : '';
}
