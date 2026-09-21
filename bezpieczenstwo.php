<?php
declare(strict_types=1);

@ini_set('display_errors', '0');
@ini_set('display_startup_errors', '0');
@ini_set('log_errors', '1');
error_reporting(E_ALL);

const DOZWOLONE_HOSTY = [
    'officeinfluencers.pl',
    'www.officeinfluencers.pl',
];

function naglowkiBezpieczenstwa(): void
{
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Cache-Control: no-store');
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

function odciskIp(): string
{
    $ip = (string)($_SERVER['REMOTE_ADDR'] ?? 'nieznany');
    return hash('sha256', $ip);
}

function limitZadan(string $nazwa, int $ile, int $okno, callable $odmowa): void
{
    $katalog = sys_get_temp_dir() . '/oi-limity';
    if (!is_dir($katalog) && !@mkdir($katalog, 0700, true) && !is_dir($katalog)) {
        return;
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

function bezZnakowSterujacych(string $wartosc): string
{
    $czysta = preg_replace('/[\x00-\x09\x0B-\x1F\x7F]/u', '', $wartosc);
    return is_string($czysta) ? $czysta : '';
}
