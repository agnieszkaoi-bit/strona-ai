<?php
/**
 * bydopamina.pl – fragmenty do wp-config.php
 *
 * Wklej PRZED linią: /* That's all, stop editing! Happy publishing. *\/
 * NIE podmieniaj całego wp-config.php tym plikiem.
 */

// 1. Klucze i sole – wygeneruj nowe: https://api.wordpress.org/secret-key/1.1/salt/
//    (zmiana wyloguje wszystkich – dobre po incydencie).

// 2. Prefiks tabel inny niż wp_ ustaw TYLKO przy nowej instalacji.
// $table_prefix = 'bd7x_';

// 3. Panel i pliki.
define( 'DISALLOW_FILE_EDIT', true );          // brak edytora motywów/wtyczek w panelu
// define( 'DISALLOW_FILE_MODS', true );       // blokada instalacji/aktualizacji z panelu – tylko jeśli aktualizujesz przez SSH/CI
define( 'FORCE_SSL_ADMIN', true );
define( 'WP_AUTO_UPDATE_CORE', 'minor' );      // automatyczne poprawki bezpieczeństwa rdzenia

// 4. Błędy nie mogą trafiać na ekran (wyciek ścieżek, zapytań SQL).
define( 'WP_DEBUG', false );
define( 'WP_DEBUG_DISPLAY', false );
define( 'WP_DEBUG_LOG', false );               // jeśli true – ustaw ścieżkę POZA public_html
@ini_set( 'display_errors', '0' );             // phpcs:ignore

// 5. Porządek i wydajność.
define( 'WP_POST_REVISIONS', 10 );
define( 'EMPTY_TRASH_DAYS', 14 );
define( 'WP_MEMORY_LIMIT', '256M' );
define( 'WP_MAX_MEMORY_LIMIT', '512M' );
// define( 'DISABLE_WP_CRON', true );           // + prawdziwy cron co 5 min: wget -qO- https://bydopamina.pl/wp-cron.php

// 6. Cookies tylko przez HTTPS.
@ini_set( 'session.cookie_secure', '1' );      // phpcs:ignore
@ini_set( 'session.cookie_httponly', '1' );    // phpcs:ignore
@ini_set( 'session.cookie_samesite', 'Lax' );  // phpcs:ignore

// 7. Ustawienia motywu (opcjonalnie nadpisują wartości z functions.php).
// define( 'BYDOPAMINA_FREE_SHIPPING_FROM', 199 );
// define( 'BYDOPAMINA_SHIPPING_CUTOFF', '14:00' );
// define( 'BYDOPAMINA_CSP_ENFORCE', true );    // po 2 tygodniach bez naruszeń w raportach CSP
