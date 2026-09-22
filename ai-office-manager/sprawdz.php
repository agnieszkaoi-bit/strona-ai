<?php
declare(strict_types=1);

/**
 * Jednorazowa diagnostyka po wgraniu plików na serwer Zenbox.
 * Otwórz w przeglądarce: https://twoja-domena.pl/ai-office-manager/sprawdz.php
 *
 * Najważniejsze: sprawdza, czy plik CSV ze zgłoszeniami NIE jest do pobrania
 * z internetu. Tego nie da się zweryfikować inaczej niż na docelowym serwerze.
 *
 * >>> PO SPRAWDZENIU USUŃ TEN PLIK Z SERWERA. <<<
 */

// Wczytujemy samą konfigurację z wyslij.php (stała blokuje obsługę formularza).
define('TYLKO_KONFIGURACJA', true);
require __DIR__ . '/wyslij.php';

$odbiorca = ODBIORCA;
$nadawca  = NADAWCA;
$plikCsv  = PLIK_CSV;
$katalog  = KATALOG_DANYCH;

// Czy katalog z danymi leży wewnątrz katalogu strony (czyli w części publicznej)?
$wewnatrzStrony = str_starts_with($katalog, __DIR__ . '/') && !str_contains($katalog, '/../');
$katalogWzgledny = $wewnatrzStrony ? substr($katalog, strlen(__DIR__) + 1) : '';

$wyniki = [];
$dodaj = static function (string $co, string $stan, string $opis) use (&$wyniki): void {
    $wyniki[] = ['co' => $co, 'stan' => $stan, 'opis' => $opis];
};

// 1. PHP
$dodaj('Wersja PHP', version_compare(PHP_VERSION, '8.0', '>=') ? 'ok' : 'uwaga',
    PHP_VERSION . (version_compare(PHP_VERSION, '8.0', '>=') ? '' : ' – zalecane PHP 8.x'));

// 2. mail()
$dodaj('Funkcja mail()', function_exists('mail') ? 'ok' : 'blad',
    function_exists('mail') ? 'dostępna' : 'wyłączona na serwerze – formularz nie wyśle maila');

// 3. Katalog na dane
if (is_dir($katalog)) {
    $dodaj('Katalog na dane', is_writable($katalog) ? 'ok' : 'blad',
        $katalog . (is_writable($katalog) ? ' – zapisywalny' : ' – BRAK PRAWA ZAPISU'));
} else {
    $dodaj('Katalog na dane', 'info',
        'jeszcze nie istnieje – powstanie przy pierwszym zgłoszeniu (' . $katalog . ')');
}

// 4. Plik CSV
$sciezkaCsv = $katalog . '/' . $plikCsv;
if (file_exists($sciezkaCsv)) {
    $wierszy = max(0, count(file($sciezkaCsv, FILE_SKIP_EMPTY_LINES)) - 1);
    $dodaj('Plik CSV', 'ok', $plikCsv . ' istnieje, ok. ' . $wierszy . ' wierszy danych');
} else {
    $dodaj('Plik CSV', 'info', 'jeszcze nie powstał – pojawi się po pierwszym zgłoszeniu');
}

// 5. NAJWAŻNIEJSZE: czy CSV jest publicznie dostępny?
if (!$wewnatrzStrony) {
    $dodaj('Dostęp do CSV z internetu', 'ok',
        'katalog z danymi leży poza katalogiem strony – plik nie jest dostępny przez WWW');
} else {
    $schemat = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host    = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $bazowy  = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
    $urlCsv  = $schemat . '://' . $host . $bazowy . '/' . $katalogWzgledny . '/' . $plikCsv;

    $kod = null;
    $kontekst = stream_context_create([
        'http' => ['method' => 'HEAD', 'timeout' => 6, 'ignore_errors' => true],
        'ssl'  => ['verify_peer' => false, 'verify_peer_name' => false],
    ]);
    $naglowkiHttp = @get_headers($urlCsv, false, $kontekst);
    if (is_array($naglowkiHttp) && preg_match('/\s(\d{3})\s/', $naglowkiHttp[0], $m) === 1) {
        $kod = (int) $m[1];
    }

    if ($kod === null) {
        $dodaj('Dostęp do CSV z internetu', 'uwaga',
            'nie udało się sprawdzić automatycznie. Otwórz ręcznie: ' . $urlCsv
            . ' – powinno pokazać błąd 403 lub 404.');
    } elseif ($kod === 200) {
        $dodaj('Dostęp do CSV z internetu', 'blad',
            'PLIK JEST PUBLICZNIE DOSTĘPNY pod ' . $urlCsv
            . ' – to wyciek danych osobowych. Napraw, zanim zbierzesz pierwsze zgłoszenie.');
    } else {
        $dodaj('Dostęp do CSV z internetu', 'ok',
            'zablokowany (HTTP ' . $kod . ') – tak ma być');
    }
}

// 6. Adres nadawcy
$domenaHosta = preg_replace('/^www\./', '', strtolower(explode(':', $_SERVER['HTTP_HOST'] ?? 'localhost')[0]));
$domenaNadawcy = strtolower(substr(strrchr($nadawca, '@') ?: '', 1));
$dodaj('Adres nadawcy', $domenaNadawcy !== '' && str_ends_with($domenaHosta, $domenaNadawcy) ? 'ok' : 'uwaga',
    $nadawca . ' | domena strony: ' . $domenaHosta
    . ($domenaNadawcy !== '' && str_ends_with($domenaHosta, $domenaNadawcy)
        ? ' – zgodne'
        : ' – RÓŻNE domeny, maile mogą trafiać do spamu'));

$dodaj('Adres odbiorcy', 'info', $odbiorca);

$ikona = ['ok' => '✓', 'blad' => '✕', 'uwaga' => '!', 'info' => 'i'];
$kolor = ['ok' => '#0a7d3c', 'blad' => '#c00050', 'uwaga' => '#b06a00', 'info' => '#63636e'];
?><!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Diagnostyka landingu</title>
<style>
  body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;
       background:#f4f4f8;color:#2e2e36;margin:0;padding:40px 20px;line-height:1.6}
  .box{max-width:760px;margin:0 auto;background:#fff;border:1px solid #e4e4ec;
       border-radius:18px;padding:32px;box-shadow:0 2px 4px rgba(0,0,0,.04)}
  h1{margin:0 0 6px;font-size:24px;color:#16161a;letter-spacing:-.02em}
  .sub{color:#63636e;font-size:14px;margin:0 0 28px}
  .row{display:flex;gap:14px;padding:16px 0;border-top:1px solid #eee;align-items:flex-start}
  .mark{flex:0 0 26px;height:26px;border-radius:50%;display:flex;align-items:center;
        justify-content:center;font-weight:700;color:#fff;font-size:14px}
  .name{font-weight:700;color:#16161a;font-size:15px}
  .desc{font-size:14px;color:#63636e;word-break:break-word}
  .foot{margin-top:28px;padding:16px 18px;border-radius:13px;background:#fdf1f7;
        border:1px solid rgba(214,0,110,.25);font-size:14px;font-weight:600;color:#a80057}
</style>
</head>
<body>
<div class="box">
  <h1>Diagnostyka landingu</h1>
  <p class="sub">OFFICE MANAGER AI OPERATIONS — sprawdzenie konfiguracji na serwerze</p>
  <?php foreach ($wyniki as $w): ?>
  <div class="row">
    <span class="mark" style="background:<?= $kolor[$w['stan']] ?>"><?= $ikona[$w['stan']] ?></span>
    <span>
      <span class="name"><?= htmlspecialchars($w['co'], ENT_QUOTES, 'UTF-8') ?></span><br>
      <span class="desc"><?= htmlspecialchars($w['opis'], ENT_QUOTES, 'UTF-8') ?></span>
    </span>
  </div>
  <?php endforeach; ?>
  <p class="foot">Po sprawdzeniu usuń plik sprawdz.php z serwera.</p>
</div>
</body>
</html>
