<?php
declare(strict_types=1);

/*
 * Klucze dostępowe formularza. Plik zostaje na serwerze: z internetu nie da
 * się go otworzyć, a jego treści nie wysyłaj nikomu (także mailem ani na czacie).
 * Przy aktualizacji folderu formularz-aiol NIE nadpisuj tego pliku.
 */
if (!defined('AIOL_KLUCZE')) {
    http_response_code(404);
    exit;
}

return [
    // Token (JWT) z Hurtowni Danych CEIDG (dane.biznes.gov.pl). Wklej go między apostrofy.
    // Puste '' = dane firm tylko z wykazu podatników VAT.
    'ceidg_token' => '',
];
