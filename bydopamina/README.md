# bydopamina.pl — szablon sklepu (Elementor Pro + WooCommerce)

Minimalistyczny, mobile-first sklep pod trendy e-commerce 2026/2027: spokojna, ciepła baza
z „dopaminowymi” akcentami, duża typografia, siatka bento, szybki checkout, dostępność
WCAG 2.2 AA (wymóg European Accessibility Act od 28.06.2025) i utwardzone bezpieczeństwo.

```
bydopamina/
├── theme/bydopamina-child/     motyw potomny Hello Elementor (wgraj jako ZIP)
│   ├── functions.php           ustawienia sklepu (próg darmowej dostawy, godzina wysyłki…)
│   ├── inc/security.php        utwardzenie WP + WooCommerce (nagłówki, CSP, limit logowań, anty card-testing)
│   ├── inc/woocommerce.php     UX sklepu: pasek darmowej dostawy, ETA wysyłki, sticky „Dodaj do koszyka”, checkout
│   ├── inc/shortcodes.php      krótkie kody używane w szablonach
│   ├── inc/performance.php     Core Web Vitals
│   └── assets/                 tokens.css (design system), main.css, main.js
├── elementor-templates/        10 szablonów JSON do importu
├── server/                     .htaccess, nginx, wp-config, robots.txt
├── docs/ARCHITEKTURA-UX.md     mapa strony, układy, decyzje UX, trendy
├── docs/BEZPIECZENSTWO.md      checklista bezpieczeństwa przed i po starcie
├── preview/index.html          statyczny podgląd strony głównej (desktop + mobile)
└── tools/build_templates.py    generator szablonów (zmień → uruchom → importuj ponownie)
```

---

## Wymagania

| Składnik | Wersja |
|---|---|
| WordPress | 6.6+ |
| PHP | 8.1+ (zalecane 8.3) |
| WooCommerce | 9.0+ |
| Elementor + Elementor Pro | 3.25+ (kontenery Flexbox włączone) |
| Motyw nadrzędny | Hello Elementor |
| HTTPS | obowiązkowo (certyfikat Let's Encrypt / hostingu) |

## Wdrożenie krok po kroku

> Najpierw na **kopii testowej (staging)**, dopiero potem na produkcji.

### 1. Motyw
1. Zainstaluj i **nie aktywuj** jeszcze motywu *Hello Elementor* (Wygląd → Motywy → Dodaj).
2. Spakuj folder `theme/bydopamina-child` do ZIP:
   `cd theme && zip -r bydopamina-child.zip bydopamina-child`
3. Wygląd → Motywy → Dodaj → Wyślij motyw → aktywuj **bydopamina Child**.

### 2. Ustawienia globalne Elementora (Elementor → Ustawienia witryny)

**Globalne kolory**

| Nazwa | HEX | Użycie |
|---|---|---|
| Primary / Tło | `#FAF8F5` | tło strony |
| Secondary / Tekst | `#121212` | tekst, przyciski |
| Text / Drugorzędny | `#56524C` | opisy, meta |
| Accent / Dopamina | `#FF4F8B` | znaczniki, hover, promocje |
| Pop / Limonka | `#D9FF50` | wyróżnienia, „Nowość” |
| Linia | `#E6E1D9` | obramowania |
| Powierzchnia | `#F2EEE8` | sekcje, tła zdjęć |

**Globalne czcionki**: Nagłówki — *Bricolage Grotesque* 600; Tekst — *Inter* 400/500.
Elementor → Ustawienia → Wydajność → **„Ładuj Google Fonts lokalnie”: Tak** (RODO — bez połączeń z serwerami Google).

**Układ**: szerokość treści 1360 px, odstęp widżetów 16 px, punkty łamania 767 / 1024 px.

**Elementor → Ustawienia → Funkcje**: włącz *Flexbox Container*, *Inline Font Icons*, *Optimized Markup*, *Optimized Control Loading*, *Lazy Load Background Images*.

### 3. Import szablonów
Szablony → Kreator motywu → **Importuj szablony** → wybierz pliki z `elementor-templates/`.

| Plik | Typ | Warunek wyświetlania |
|---|---|---|
| `01-header.json` | Header | Cała witryna |
| `02-footer.json` | Footer | Cała witryna |
| `04-karta-produktu.json` | Pojedynczy produkt | Wszystkie produkty |
| `05-sklep-archiwum.json` | Archiwum produktów | Wszystkie archiwa produktów |
| `10-404.json` | Strona 404 | Strona 404 |
| `03-strona-glowna.json` | Strona | wstaw w stronę „Start” |
| `06-koszyk.json` | Strona | wstaw w stronę „Koszyk” |
| `07-zamowienie.json` | Strona | wstaw w stronę „Zamówienie” |
| `08-moje-konto.json` | Strona | wstaw w stronę „Moje konto” |
| `09-kontakt.json` | Strona | wstaw w stronę „Kontakt” |

Szablony typu *Strona*: otwórz stronę w Elementorze → ikona folderu → Moje szablony → Wstaw.

Po imporcie:
- **Header** → widget *Menu nawigacyjne* → wybierz menu „Główne” (utwórz w Wygląd → Menu: Nowości, Kategorie…, O nas).
- **Zdjęcia**: podmień placeholdery. Hero: min. 1600×2000 px, WebP/AVIF; do zdjęcia hero dodaj klasę CSS `bd-lcp` (priorytetowe ładowanie).
- **Kafle bento**: w każdym kaflu ustaw zdjęcie tła i link kategorii.
- **Formularz newslettera**: podłącz akcję MailerLite / Mailchimp / Brevo w *Akcje po wysłaniu* i włącz double opt-in.
- WooCommerce → Ustawienia → Zaawansowane: przypisz strony Koszyk / Zamówienie / Moje konto.

### 4. Ustawienia sklepu (functions.php lub wp-config.php)

| Stała | Domyślnie | Opis |
|---|---|---|
| `BYDOPAMINA_FREE_SHIPPING_FROM` | `199` | próg darmowej dostawy (zł) — ustaw ten sam w WooCommerce → Wysyłka |
| `BYDOPAMINA_SHIPPING_CUTOFF` | `14:00` | godzina graniczna wysyłki tego samego dnia |
| `BYDOPAMINA_RETURN_DAYS` | `30` | dni na zwrot (argumenty zaufania) |
| `BYDOPAMINA_CSP_ENFORCE` | `false` | `true` = CSP wymuszany (włącz po 2 tyg. testów) |
| `BYDOPAMINA_SEND_HEADERS` | `true` | `false`, jeśli nagłówki ustawia `.htaccess` / nginx |

### 5. Serwer i bezpieczeństwo
Wykonaj **docs/BEZPIECZENSTWO.md** — w tym `.htaccess` lub `nginx`, `wp-config`, 2FA, WAF i kopie.

## Krótkie kody (widget „Shortcode”)

| Kod | Gdzie | Co robi |
|---|---|---|
| `[bd_free_shipping_bar]` | koszyk, produkt, mini-koszyk (auto) | pasek postępu do darmowej dostawy, odświeżany AJAX |
| `[bd_delivery_eta]` | produkt | „Zamów w ciągu 2 h 15 min – wyślemy dziś” (pomija weekendy; święta: filtr `bydopamina_holidays`) |
| `[bd_trust_badges variant="row\|stack"]` | home, produkt, koszyk | 4 argumenty zaufania |
| `[bd_category_chips]` | sklep / kategorie | przewijane „chipsy” kategorii |
| `[bd_mobile_nav]` | stopka | dolny pasek nawigacji na telefonie (strefa kciuka) |
| `[bd_product_reviews]` | produkt | opinie + formularz |
| `[bd_year]` | stopka | bieżący rok |

## Zalecane wtyczki (minimum — każda wtyczka to potencjalna luka)

| Cel | Wtyczka | Uwagi |
|---|---|---|
| Płatności | Przelewy24 (BLIK, karty, Apple/Google Pay) lub PayU / Stripe | oficjalne wtyczki operatorów |
| BNPL | PayPo | „kup teraz, zapłać za 30 dni” |
| Dostawa | InPost PL (Paczkomaty + geowidget), DPD | mapa wyboru paczkomatu w checkout |
| Omnibus | *Omnibus — show the lowest price* | obowiązkowa najniższa cena z 30 dni przy promocjach |
| Prawo | Regulaminy z kancelarii / generator + WP Consent API | regulamin, polityka prywatności, zwroty |
| Zgody cookies | Complianz / CookieYes (Google Consent Mode v2) | wymagane dla GA4/Ads |
| Bezpieczeństwo | Wordfence **lub** Patchstack + *Two-Factor* | jedna wtyczka firewall, nie kilka |
| Kopie | UpdraftPlus → zewnętrzny magazyn (S3/Drive) | lub kopie hostingu z retencją 30 dni |
| Cache | LiteSpeed Cache (na LiteSpeed) / WP Rocket | wyklucz koszyk, zamówienie, moje konto |
| SEO | Rank Math / Yoast + dane strukturalne Product | |
| Warianty | Variation Swatches for WooCommerce | kolory/rozmiary jako „pigułki” |
| Wyszukiwarka (opcja) | FiboSearch | podpowiedzi na żywo ze zdjęciami |
| Faktury | integracja z Fakturownia / inFakt / wFirma + KSeF | |

## Zmiana szablonów
Edytuj `tools/build_templates.py`, uruchom `python3 tools/build_templates.py` i zaimportuj pliki ponownie.
Kolory, fonty, promienie, odstępy: tylko `assets/css/tokens.css` (+ Globalne kolory w Elementorze).
