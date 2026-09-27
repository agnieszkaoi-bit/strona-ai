# Bezpieczeństwo — bydopamina.pl

Zabezpieczenie sklepu to warstwy. Motyw pokrywa warstwę aplikacji; resztę musisz ustawić
na hostingu, w DNS i w procesach. Nic nie daje 100% ochrony — celem jest, żeby atak był
drogi, wykryty szybko i odwracalny (kopie).

```
[ Cloudflare WAF / DDoS / bot fight ]  →  [ Serwer: .htaccess / nginx, PHP 8.3, izolacja ]
      →  [ WordPress: motyw inc/security.php + 1 wtyczka firewall + 2FA ]
      →  [ WooCommerce: rate limit Store API, honeypot, limit nieudanych płatności ]
      →  [ Procesy: aktualizacje, kopie 3-2-1, monitoring, minimum uprawnień ]
```

## Co robi motyw (automatycznie)

| Zagrożenie | Ochrona | Plik |
|---|---|---|
| Brute-force logowania | limit 5 prób / 15 min na IP, ogólny komunikat błędu | `inc/security.php` §4 |
| XML-RPC multicall / pingback DDoS | XML-RPC wyłączony | §2 (+ `.htaccess`) |
| Enumeracja loginów | blokada `?author=`, `/wp-json/wp/v2/users`, sitemapy użytkowników, oEmbed | §3 |
| XSS, clickjacking, sniffing | CSP (tryb raportu → wymuszanie), X-Frame-Options, nosniff, Referrer-Policy, Permissions-Policy, HSTS | §5 |
| Edycja kodu po przejęciu konta admina | `DISALLOW_FILE_EDIT` | §6 |
| Klienci w panelu | przekierowanie z `/wp-admin` do *Moje konto*, brak paska admina | §6 |
| Złośliwe pliki w mediach | blokada SVG / HTML / JS / EXE w uploadzie | §7 |
| Card testing (testowanie kradzionych kart) | rate limit Store API (25 żądań / 10 s), limit 5 nieudanych płatności / h na IP | §8 |
| Boty rejestrujące konta / spam zamówień | honeypot w rejestracji i checkout, blokada jednorazowych e-maili | §8 |
| Spam formularzy Elementora | pole *Honeypot* w każdym formularzu (newsletter, kontakt) | szablony |
| Rozpoznanie wersji | brak wersji WP/WooCommerce w kodzie strony | §1 |

## Checklista przed startem

### Hosting i serwer
- [ ] Hosting z izolacją kont, PHP **8.3**, codziennymi kopiami i SSH/SFTP (bez zwykłego FTP).
- [ ] Certyfikat SSL, przekierowanie na `https://bydopamina.pl` (bez www) — `server/.htaccess`.
- [ ] Wklej `server/.htaccess` (Apache/LiteSpeed) **albo** `server/nginx-bydopamina.conf` (nginx). Test: strona, koszyk, checkout, panel.
- [ ] Uprawnienia: katalogi `755`, pliki `644`, `wp-config.php` **`400`/`440`**.
- [ ] `wp-config.php`: fragmenty z `server/wp-config-hardening.php`, **nowe klucze i sole**.
- [ ] Baza danych: osobny użytkownik tylko dla tej bazy, silne hasło, prefiks tabel ≠ `wp_` (przy nowej instalacji).
- [ ] Usuń `readme.html`, `license.txt`, nieużywane motywy (zostaw Hello Elementor + child + 1 domyślny) i nieaktywne wtyczki.

### Cloudflare (plan Free wystarczy na start)
- [ ] DNS przez Cloudflare (proxy ☁ włączone), SSL/TLS: **Full (strict)**, *Always Use HTTPS*, TLS ≥ 1.2.
- [ ] Security → WAF → **Managed rules** (Free: *Cloudflare Free Managed Ruleset*) + *Bot Fight Mode*.
- [ ] Reguły WAF (Custom rules):
  - `(http.request.uri.path eq "/wp-login.php") and not ip.src in {TWOJE_IP}` → **Managed Challenge**
  - `(http.request.uri.path contains "/xmlrpc.php")` → **Block**
  - `(http.request.uri.path contains "/wp-admin") and not http.request.uri.path contains "admin-ajax.php" and not ip.src.country in {"PL"}` → **Managed Challenge**
- [ ] Rate limiting: `/wp-login.php` 5 żądań / min; `/?wc-ajax=checkout` i `/wp-json/wc/store/v1/checkout` 10 / min na IP.
- [ ] Na serwerze przywróć prawdziwe IP odwiedzających (`mod_remoteip` / `real_ip_header CF-Connecting-IP`) — inaczej limity z motywu liczą IP Cloudflare.
- [ ] Opcjonalnie: **Turnstile** (zamiast reCAPTCHA, bez cookies) w logowaniu, rejestracji i checkout — wtyczka *Simple Cloudflare Turnstile*.

### Konta i dostęp
- [ ] Login administratora ≠ `admin`, nazwa wyświetlana ≠ login.
- [ ] **2FA** dla wszystkich z rolą Administrator / Kierownik sklepu (Wordfence Login Security lub *Two-Factor*; klucze sprzętowe/passkeys, jeśli możliwe).
- [ ] Hasła 16+ znaków z menedżera haseł; osobne konta dla każdej osoby; zasada najmniejszych uprawnień (obsługa zamówień = *Kierownik sklepu*, nie Administrator).
- [ ] E-mail administratora na skrzynce z 2FA (reset hasła = klucz do sklepu).
- [ ] Przegląd kont co kwartał; usuwanie kont byłych współpracowników i agencji.

### WordPress i wtyczki
- [ ] **Jedna** wtyczka firewall/skaner: Wordfence *albo* Patchstack (wirtualne łatki) — nie kilka naraz.
- [ ] Automatyczne aktualizacje: poprawki rdzenia (`minor`), wtyczki o dobrej historii — auto; WooCommerce/Elementor — ręcznie po teście na stagingu w ciągu 48 h od wydania.
- [ ] Wtyczki tylko z wordpress.org lub od producenta, z aktywnym rozwojem (aktualizacja < 6 mies.). **Nigdy „nulled”**.
- [ ] Wyłącz rejestrację kont WordPress (*Ustawienia → Ogólne → Każdy może się zarejestrować: NIE*); konta klientów tworzy WooCommerce.
- [ ] WooCommerce → Ustawienia → Konta: zakup bez konta **włączony**, generowanie hasła automatyczne.
- [ ] Komentarze na blogu wyłączone lub z moderacją; opinie produktów tylko od kupujących („Tylko zweryfikowani właściciele”).
- [ ] Klucze REST API WooCommerce — tylko potrzebne, z minimalnymi uprawnieniami (Read), usuń nieużywane; hasła aplikacji wyłączone, jeśli nie są potrzebne.

### Płatności i dane (RODO / PCI DSS)
- [ ] Płatności wyłącznie przez bramkę z przekierowaniem lub iframe (Przelewy24 / PayU / Stripe) — dane kart **nigdy** nie przechodzą przez serwer (PCI DSS SAQ A).
- [ ] Webhooki bramki z weryfikacją podpisu (ustaw klucz CRC/sekret w wtyczce).
- [ ] Włącz 3-D Secure i powiadomienia o podejrzanych transakcjach w panelu operatora.
- [ ] Umowy powierzenia (DPA) z hostingiem, operatorem płatności, systemem mailingowym, kurierem.
- [ ] Rejestr czynności przetwarzania i okresy retencji (WooCommerce → Ustawienia → Konta → Przechowywanie danych osobowych).
- [ ] Polityka prywatności i cookies, baner z Consent Mode v2 (Complianz/CookieYes).

### Kopie zapasowe (3-2-1)
- [ ] Codziennie baza, co tydzień pliki; **poza serwerem** (S3/Backblaze/Google Drive), retencja 30 dni.
- [ ] Przed każdą aktualizacją WooCommerce/Elementora — kopia ręczna.
- [ ] **Test odtworzenia** co kwartał na stagingu (kopia, której nie odtworzono, nie istnieje).

### Monitoring
- [ ] Uptime (UptimeRobot / Better Stack) z alertem SMS/e-mail.
- [ ] Alerty Wordfence/Patchstack o nowych podatnościach wtyczek.
- [ ] Google Search Console (ostrzeżenia o złośliwym oprogramowaniu, zmiany indeksu).
- [ ] Raporty CSP: przez 2 tygodnie przeglądaj konsolę przeglądarki (*Report-Only*) na: home, produkt, koszyk, checkout (z każdą metodą płatności i mapą InPost), konto. Dodaj brakujące domeny w `inc/security.php`, potem `BYDOPAMINA_CSP_ENFORCE = true`.

## Test po wdrożeniu
```bash
# Nagłówki
curl -sI https://bydopamina.pl | grep -iE "strict-transport|content-security|x-frame|x-content|referrer|permissions"
# XML-RPC zablokowany (oczekiwane 403/405)
curl -s -o /dev/null -w "%{http_code}\n" -X POST https://bydopamina.pl/xmlrpc.php
# Enumeracja użytkowników (oczekiwane 403 lub przekierowanie, brak loginu w URL)
curl -sI "https://bydopamina.pl/?author=1" | head -3
curl -s https://bydopamina.pl/wp-json/wp/v2/users   # oczekiwane: rest_no_route
# PHP w uploads (oczekiwane 403)
curl -s -o /dev/null -w "%{http_code}\n" https://bydopamina.pl/wp-content/uploads/test.php
```
Zewnętrznie: **securityheaders.com** (cel: A), **SSL Labs** (cel: A+), **WPScan** / Patchstack (podatności wtyczek).

## Plan na incydent (wydrukuj)
1. Tryb konserwacji / Cloudflare *Under Attack Mode*.
2. Zmień hasła: hosting, baza, wszyscy administratorzy, e-mail admina; **nowe sole** w `wp-config.php` (wylogowuje wszystkich).
3. Skan (Wordfence), porównanie plików rdzenia z oryginałem (`wp core verify-checksums`, `wp plugin verify-checksums --all`).
4. Przywróć czystą kopię sprzed incydentu, zaktualizuj wszystko, usuń podatną wtyczkę.
5. Jeśli wyciekły dane osobowe: zgłoszenie do **UODO w 72 h**, powiadomienie klientów, gdy wysokie ryzyko; incydent płatniczy → operator płatności.
6. Wnioski: co zawiodło, jaka warstwa by to zatrzymała.
