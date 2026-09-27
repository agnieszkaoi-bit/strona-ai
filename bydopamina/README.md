# bydopamina.pl — sklep z biżuterią (Elementor Pro + WooCommerce)

Szablon sklepu z biżuterią (złoto 18K na stali 316L) w kierunku **editorial 2026/27**: zamiast typowego
„szablonu AI” (kafle bento, gradienty, kropki nad nagłówkami, trzy identyczne karty opinii) — układ jak
w magazynie modowym: asymetryczna siatka, duży szeryf *Instrument Serif* z kursywą, etykiety w *Geist Mono*,
numerowane sekcje, kąty proste, cienkie linie. Kolor robi fotografia; interfejs jest cichy, a „dopamina”
to jeden akcent — wiśnia `#9C1C33` (promocje, licznik koszyka, hover przycisków).

Najważniejsze elementy UX dla biżuterii: kategorie w łukach, zakładki Bestsellery/Nowości/Promocje,
druga fotka (na modelce) po najechaniu, **shop the look** z punktami na zdjęciu, **rozmiarówka** w panelu
(pierścionki + długości łańcuszków), sekcja o materiale (316L / 18K / 0 niklu), prezent wg budżetu,
**pakowanie na prezent** w checkoutcie, prawdziwe opinie z WooCommerce z automatyczną średnią,
dolna nawigacja i przyklejony „Dodaj do koszyka” na telefonie. Dostępność WCAG 2.2 AA (wymóg EAA od 28.06.2025).

```
bydopamina/
├── theme/bydopamina-child/     motyw potomny Hello Elementor (wgraj jako ZIP)
│   ├── functions.php           ustawienia sklepu (próg darmowej dostawy, godzina wysyłki…)
│   ├── inc/security.php        utwardzenie WP + WooCommerce (nagłówki, CSP, limit logowań, anty card-testing)
│   ├── inc/woocommerce.php     UX sklepu: pasek darmowej dostawy, ETA wysyłki, sticky „Dodaj do koszyka”, checkout
│   ├── inc/shortcodes.php      krótkie kody używane w szablonach
│   ├── inc/performance.php     Core Web Vitals
│   └── assets/                 tokens.css (design system), main.css, main.js, fonts/ (lokalne woff2)
├── elementor-templates/        10 szablonów JSON do importu
├── server/                     .htaccess, nginx, wp-config, robots.txt
├── docs/ARCHITEKTURA-UX.md     mapa strony, układy, decyzje UX, trendy
├── docs/BEZPIECZENSTWO.md      checklista bezpieczeństwa przed i po starcie
├── preview/                    statyczny podgląd: index.html (strona główna), produkt.html (karta produktu)
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
| Tło — kość słoniowa | `#F6F2EC` | tło strony |
| Tekst — espresso | `#1C1917` | tekst, przyciski, stopka |
| Tekst drugorzędny | `#5E564E` | opisy, etykiety |
| Wiśnia (akcent) | `#9C1C33` | promocje, licznik koszyka, hover CTA |
| Piasek | `#EDE6DC` | tła zdjęć, sekcja prezentów |
| Masło | `#F1E4B3` | etykieta „Nowość” |
| Złoto (tylko grafika) | `#A8864F` | ikony, gwiazdki, linie — nigdy tekst |
| Linia | `#DCD2C4` | obramowania |

**Czcionki**: *Instrument Serif* (nagłówki), *Geist* (tekst), *Geist Mono* (etykiety, ceny w tabelach).
Motyw ładuje je **lokalnie** z `assets/fonts/` (RODO, CSP). W Elementorze: Ustawienia → Zaawansowane →
**Google Fonts: Wyłącz**, a w Globalnych czcionkach zostaw „Domyślne” — style nadaje motyw.

**Układ**: szerokość treści 1520 px, odstęp widżetów 16 px, punkty łamania 767 / 1024 px.

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
- **Zdjęcia**: podmień placeholdery (WebP/AVIF). Hero główne 2000×1600 px (5:4), boczne 1200×1200 px; packshoty produktów 4:5 na jednolitym, ciepłym tle (#EDE6DC) + **druga fotka na modelce** jako pierwsze zdjęcie galerii (pokazuje się po najechaniu). Zdjęcie hero ma już klasę `bd-lcp` (priorytetowe ładowanie).
- **Kategorie w łukach**: Produkty → Kategorie → ustaw miniaturę każdej kategorii (3:4) i kolejność.
- **Shop the look**: w shortcodzie wpisz ID zdjęcia i ID produktów z pozycją punktu, np. `products="101:34:38,102:52:30"`.
- **Formularz newslettera**: podłącz akcję MailerLite / Mailchimp / Brevo w *Akcje po wysłaniu* i włącz double opt-in.
- WooCommerce → Ustawienia → Zaawansowane: przypisz strony Koszyk / Zamówienie / Moje konto.

### 4. Ustawienia sklepu (functions.php lub wp-config.php)

| Stała | Domyślnie | Opis |
|---|---|---|
| `BYDOPAMINA_FREE_SHIPPING_FROM` | `199` | próg darmowej dostawy (zł) — ustaw ten sam w WooCommerce → Wysyłka |
| `BYDOPAMINA_SHIPPING_CUTOFF` | `14:00` | godzina graniczna wysyłki tego samego dnia |
| `BYDOPAMINA_RETURN_DAYS` | `30` | dni na zwrot (argumenty zaufania) |
| `BYDOPAMINA_GIFTWRAP_PRICE` | `9` | cena pakowania na prezent w checkoutcie (zł) |
| `BYDOPAMINA_CSP_ENFORCE` | `false` | `true` = CSP wymuszany (włącz po 2 tyg. testów) |
| `BYDOPAMINA_SEND_HEADERS` | `true` | `false`, jeśli nagłówki ustawia `.htaccess` / nginx |

### 5. Serwer i bezpieczeństwo
Wykonaj **docs/BEZPIECZENSTWO.md** — w tym `.htaccess` lub `nginx`, `wp-config`, 2FA, WAF i kopie.

## Krótkie kody (widget „Shortcode”)

| Kod | Gdzie | Co robi |
|---|---|---|
| `[bd_usp variant="row\|list"]` | home, produkt | 4 cechy materiału: 18K na 316L, hipoalergiczna, wodoodporna, nie ciemnieje (filtr `bydopamina_usp`) |
| `[bd_category_arches limit="6"]` | home | kategorie w łukach, kolejność z Produkty → Kategorie, zdjęcie = miniatura kategorii |
| `[bd_product_tabs limit="8"]` | home, 404 | zakładki Bestsellery / Nowości / Promocje (ARIA, strzałki na klawiaturze) |
| `[bd_shop_the_look image="ID" products="ID:x:y,…"]` | home | zdjęcie stylizacji z punktami (x, y w % od lewej/góry) + lista produktów |
| `[bd_gift_finder]` | home | prezent wg budżetu: do 79 / 129 / 199 zł (filtr `bydopamina_gift_ranges`) |
| `[bd_rating_summary]` | home | średnia ocena i liczba opinii liczone z WooCommerce |
| `[bd_reviews limit="10"]` | home | ostatnie opinie 4–5★ **tylko od zweryfikowanych kupujących** |
| `[bd_size_guide]` | produkt | link „Rozmiarówka” + panel z tabelą rozmiarów pierścionków i długości łańcuszków |
| `[bd_product_claims]` | produkt | etykiety: Nowość (30 dni) / 18K złoto / Stal 316L / Wodoodporna (filtr `bydopamina_product_claims`) |
| `[bd_delivery_eta]` | produkt | „Zamów w ciągu 2 h 15 min – wyślemy dziś” (pomija weekendy; święta: filtr `bydopamina_holidays`) |
| `[bd_free_shipping_bar]` | koszyk, produkt, mini-koszyk (auto) | pasek do darmowej dostawy, odświeżany AJAX |
| `[bd_trust_badges]` | koszyk | dostawa, zwroty, pudełko, płatności |
| `[bd_category_chips]` | sklep / kategorie | podkategorie z liczbą produktów |
| `[bd_mobile_nav]` | stopka | dolny pasek: Start · Sklep · Szukaj · Ulubione · Koszyk |
| `[bd_product_reviews]` | produkt | opinie + formularz (w akordeonie) |
| `[bd_year]` | stopka | bieżący rok |

Automatycznie (bez shortcode'ów): druga fotka z galerii po najechaniu na kartę produktu, etykiety
„Nowość”/„Wyprzedane”, rabat „−20%”, **pakowanie na prezent** w checkoutcie (`BYDOPAMINA_GIFTWRAP_PRICE`),
przyklejony „Dodaj do koszyka” na telefonie, wyszukiwarka zwracająca produkty.

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
| Warianty | Variation Swatches for WooCommerce | kolor złoty/srebrny i rozmiary jako przyciski (style w motywie) |
| Ulubione | TI WooCommerce Wishlist | strona `/lista-zyczen/` (ikona serca w headerze i dolnym pasku) |
| Opinie ze zdjęciami | Customer Reviews for WooCommerce lub TrustMate | prośba o opinię po dostawie, zdjęcia klientów, weryfikacja zakupu |
| Wyszukiwarka (opcja) | FiboSearch | podpowiedzi na żywo ze zdjęciami |
| Faktury | integracja z Fakturownia / inFakt / wFirma + KSeF | |

## Zmiana szablonów
Edytuj `tools/build_templates.py`, uruchom `python3 tools/build_templates.py` i zaimportuj pliki ponownie.
Kolory, fonty, promienie, odstępy: tylko `assets/css/tokens.css` (+ Globalne kolory w Elementorze).
