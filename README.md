# arkconsulting.com.pl — statyczna strona ARK Consulting

Statyczna (poza WordPress) wersja strony firmy szkoleniowej **ARK Consulting
Agnieszka Korach**, przygotowana pod hosting **Zenbox** oraz maksymalnie
zoptymalizowana pod SEO, GEO/AEO (widoczność w Google i w odpowiedziach
modeli AI) oraz roboty wyszukiwarek.

## Struktura serwisu

```
/                                    strona główna
/szkolenia/                          hub — pełna oferta szkoleń pracowników
/szkolenia/szkolenia-ai-dla-pracownikow.html   szkolenia z AI
/szkolenia/szkolenie-dla-managera.html         szkolenie dla managera
/szkolenia/odpornosc-psychiczna.html           odporność psychiczna zespołów
/akademia-asystentek/                szkolenie dla asystentki (Akademia Asystentek)
/o-firmie.html                       o firmie
/eksperci/agnieszka-korach.html      profil eksperta
/blog/                               blog (artykuły eksperckie)
/kontakt.html                        formularz kontaktowy + dane
/polityka-prywatnosci.html           polityka prywatności (RODO)
/regulamin.html                      regulamin szkoleń i serwisu
/cookies.html                        polityka cookies
/dziekujemy.html                     strona po wysłaniu formularza (noindex)
/404.html                            strona błędu 404
/wyslij-wiadomosc.php                obsługa formularza kontaktowego (PHP + mail())
/robots.txt, /sitemap.xml, /llms.txt pliki dla robotów i crawlerów AI
/.well-known/security.txt            kontakt do zgłaszania luk bezpieczeństwa
/.htaccess                           konfiguracja Apache/LiteSpeed (Zenbox) + nagłówki bezpieczeństwa
/assets/.htaccess                    blokada wykonywania PHP w folderze zasobów
/assets/css/style.css                wspólny arkusz stylów
/assets/js/main.js                   nawigacja, animacje, FAQ, karuzela
/assets/js/cookie-consent.js         baner zgody na cookies (RODO)
/assets/img/                         grafiki — patrz IMAGES-NEEDED.md
```

## Optymalizacja SEO / GEO / AEO

- **Dane strukturalne (Schema.org / JSON-LD)** na każdej stronie:
  `EducationalOrganization`, `Course`, `FAQPage`, `Person`, `BreadcrumbList`,
  `BlogPosting` — spójne w całym serwisie (ta sama nazwa, adres, NIP, opis).
- **Treść budowana pod słowa kluczowe**: „szkolenia pracowników", „szkolenia
  z AI", „szkolenie dla managera", „szkolenie dla asystentki" — każda fraza
  ma dedykowaną, rozbudowaną podstronę zamiast cienkiej strony pod wariant.
- **FAQ w formacie pytanie → pełna, samodzielna odpowiedź w pierwszym zdaniu**
  — pod ekstrakcję fragmentów przez Google (AI Overviews) i modele AI.
- **robots.txt** świadomie dopuszcza crawlery AI (GPTBot, ClaudeBot,
  PerplexityBot, Google-Extended i inne) — strona ma być widoczna zarówno
  w Google, jak i w odpowiedziach asystentów AI.
- **llms.txt** — dodany na wyraźną prośbę; w praktyce Google go nie
  wykorzystuje, a większość crawlerów AI pomija ten plik i czyta HTML
  bezpośrednio. Nie szkodzi, ale realną robotę wykonuje jakość treści HTML.
- **Semantyczny HTML5**, nagłówki H1–H3 w logicznej hierarchii, `alt` przy
  obrazach, `aria-label`/`aria-current` w nawigacji, skip-link.
- **Wydajność**: jeden wspólny plik CSS/JS (cache w przeglądarce),
  `content-visibility:auto` poniżej pierwszego ekranu, `font-display:swap`,
  `loading="lazy"` na zdjęciach poza hero.

## Cookies i RODO

Baner zgody (`assets/js/cookie-consent.js`) rozróżnia cookies niezbędne,
analityczne i marketingowe, zapisuje decyzję w `localStorage` i integruje się
z Google Consent Mode v2 (gotowe pod przyszłe dodanie GA4/Google Ads — samo
w sobie niczego nie ładuje). Ustawienia można zmienić w dowolnym momencie
linkiem „Ustawienia cookies" w stopce.

## Bezpieczeństwo

Strona jest statyczna (brak bazy danych, brak panelu logowania, brak
WordPressa), więc powierzchnia ataku jest z natury bardzo mała. Jedyny
fragment kodu wykonywanego po stronie serwera to `wyslij-wiadomosc.php`
(obsługa formularza kontaktowego) — to na nim skupiają się poniższe
zabezpieczenia:

- **Ochrona przed header/email injection**: wszystkie pola trafiające do
  nagłówków e-maila mają usuwane znaki CR/LF i inne znaki sterujące, więc
  nie da się przez formularz wstrzyknąć dodatkowych nagłówków (np. `Bcc:`)
  i wykorzystać go do wysyłki spamu.
- **Stały nadawca**: nagłówek `From` zawsze wskazuje Twoją domenę — dane od
  użytkownika trafiają wyłącznie do `Reply-To`. Zmniejsza to ryzyko
  podszywania się i trafiania maili do SPAM-u.
- **Honeypot + test czasowy**: niewidoczne dla człowieka pole `website` oraz
  odrzucanie zgłoszeń wysłanych błyskawicznie (< 3 s od załadowania strony)
  odsiewają większość botów bez potrzeby CAPTCHA. Brak JavaScriptu nie
  blokuje wysyłki — degraduje się bezpiecznie.
- **Limity długości i lista dozwolonych tematów**: pola mają twarde limity
  znaków, a pole „Temat" akceptuje tylko wartości z zamkniętej listy.
- **Błędy PHP nigdy nie trafiają na ekran** (`display_errors` wyłączone) —
  brak ryzyka ujawnienia ścieżek serwera w komunikacie błędu.
- **Tylko metoda POST** jest akceptowana przez skrypt.

Na poziomie całego serwisu (`.htaccess`):

- **Wymuszone HTTPS** oraz nagłówek `Strict-Transport-Security` (HSTS).
- **Content-Security-Policy** dopuszczająca wyłącznie zasoby faktycznie
  używane przez stronę (Google Fonts, MailerLite) — blokuje ładowanie
  skryptów/stylów z dowolnych obcych domen, nawet gdyby ktoś zdołał
  wstrzyknąć taki tag w treść strony.
- **X-Content-Type-Options, X-Frame-Options, Referrer-Policy,
  Permissions-Policy** — standardowy zestaw nagłówków ograniczających
  clickjacking, MIME-sniffing i dostęp do kamery/mikrofonu/lokalizacji.
- **Blokada wykonywania plików PHP w `/assets/`** (`assets/.htaccess`) — na
  wypadek gdyby ktoś kiedyś wgrał tam złośliwy plik, serwer odmówi jego
  uruchomienia.
- **Blokada bezpośredniego dostępu** do `.htaccess`, `.env`, `.git*`,
  `README.md`, `IMAGES-NEEDED.md` i podobnych plików.
- **Blokada metod TRACE/TRACK/CONNECT** (ochrona przed Cross-Site Tracing).
- **Blokada typowych pozostałości po WordPressie** (`wp-admin/`,
  `wp-login.php`, `xmlrpc.php`, `wp-config.php`) na wypadek, gdyby stare
  pliki nadal fizycznie leżały na serwerze.
- **`/.well-known/security.txt`** — standardowy (RFC 9116) adres do
  zgłaszania luk bezpieczeństwa przez badaczy.

### To, co musisz zrobić Ty (poza kodem)

Kod ogranicza ryzyko tam, gdzie może — reszta zależy od konfiguracji konta:

1. **Usuń stare pliki WordPressa z serwera Zenbox** (`wp-admin/`,
   `wp-content/`, `wp-includes/`, `wp-login.php`, `xmlrpc.php`,
   `wp-config.php` itd.), jeśli nadal tam są — to najważniejszy krok. Same
   reguły w `.htaccess` blokują dostęp do najbardziej newralgicznych plików,
   ale usunięcie ich fizycznie jest jedynym pewnym zabezpieczeniem.
2. **Zmień hasło do panelu Zenbox i do FTP/SFTP** na nowe, unikalne, jeśli
   było używane wcześniej przy WordPressie — a jeśli Zenbox oferuje 2FA
   (weryfikację dwuetapową) do panelu klienta, włącz ją.
   Stare hasło administratora WordPressa nie ma już zastosowania po
   migracji, ale warto też je unieważnić (np. zmieniając/usuwając konto
   admina), jeśli baza danych WP nadal istnieje na serwerze.
3. **Włącz certyfikat SSL** (patrz sekcja „Wdrożenie" niżej) — bez niego
   nagłówek HSTS i wymuszenie HTTPS w `.htaccess` nie zadziałają poprawnie.
4. **Rób kopie zapasowe** — Zenbox zwykle oferuje automatyczne backupy w
   panelu; upewnij się, że są włączone.
5. Jeśli po wdrożeniu przestanie działać formularz zapisu do newslettera,
   sprawdź w konsoli przeglądarki (F12) komunikaty `Content-Security-Policy`
   — oznacza to, że MailerLite ładuje zasób z domeny, której nie ma na
   liście dozwolonych w `.htaccess`; wystarczy dopisać tę domenę do
   odpowiedniej dyrektywy (`script-src` / `connect-src` / `form-action`).

## Wdrożenie na Zenbox

1. Zaloguj się do panelu Zenbox → **Menedżer plików** (albo połącz się przez
   FTP/SFTP danymi z panelu).
2. Wgraj **całą zawartość** tego repozytorium do katalogu głównego domeny
   (zwykle `public_html/`), zachowując strukturę folderów.
3. Uzupełnij brakujące zdjęcia — pełna lista i wymagane nazwy plików w
   [`IMAGES-NEEDED.md`](./IMAGES-NEEDED.md).
4. W `wyslij-wiadomosc.php` sprawdź adres w zmiennej `$odbiorca` (domyślnie
   `szkolenia@arkconsulting.com.pl`) i upewnij się, że ta skrzynka istnieje
   w panelu Zenbox.
5. Włącz certyfikat SSL dla domeny w panelu Zenbox (zwykle darmowy, Let's
   Encrypt) — `.htaccess` wymusza HTTPS.
6. Po publikacji zgłoś `sitemap.xml` w Google Search Console i Bing Webmaster
   Tools.
7. Zweryfikuj dane firmy w Google Business Profile, żeby były identyczne jak
   na stronie (nazwa, adres, telefon, NIP) — spójność danych pomaga zarówno
   w Google, jak i w odpowiedziach modeli AI.

## Do weryfikacji przez właściciela strony

- **NIP w danych firmy**: na starej stronie pojawiały się dwie różne wartości
  (`529-132-74-05` w stopce i `5291466035` w zgodzie na newsletter). W tej
  wersji ujednolicono do `529-132-74-05` — proszę potwierdzić, że to
  poprawny numer.
- **Terminy szkoleń otwartych** na stronie głównej są przykładowe
  (dopasowane do daty wdrożenia) — do podmiany na rzeczywisty kalendarz.
- **Zdjęcia** — patrz `IMAGES-NEEDED.md`.
