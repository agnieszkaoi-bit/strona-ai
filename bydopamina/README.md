# bydopamina.pl — sklep z kolorową biżuterią (Elementor Pro + WooCommerce)

**Kierunek (v3): kobieco, jasno, czytelnie – i prosto do koszyka.** Biżuteria jest kolorowa, więc strona
jest stonowana: porcelanowa biel, pudrowy róż, ciepła czerń, jeden akcent – malina `#A3385A`.
Jeden prosty, bardzo czytelny krój: **Figtree** (tekst 17 px). Miękkie kształty: zaokrąglenia, przyciski „pigułki”.

**Wyróżniki (czego nie mają inne sklepy):**
1. **Łuk** jako znak rozpoznawczy – zdjęcie w hero, kategorie, karty nastrojów.
2. **„Jak chcesz się dziś poczuć?”** – biżuteria wg nastroju (Radość, Energia, Czułość, Spokój, Marzenia, Świeżość),
   każdy nastrój = paleta kolorów. Nawiązuje do nazwy marki i ułatwia wybór z kolorowej oferty.
3. Logo z malinową kropką `bydopamina.` powtórzone w wielkim napisie w stopce.

**Ścieżka zakupu i upsell (sklep ~50 produktów na start):**
hero z jednym przyciskiem → kategorie → bestsellery z szybkim „+” (dodanie bez wchodzenia w produkt) →
karta produktu z **„Dobierz komplet”** (zaznacz i dodaj 2–3 pasujące produkty jednym kliknięciem) →
mini-koszyk z paskiem do darmowej dostawy i **„Pasuje do tego”** → koszyk z sprzedażą krzyżową →
zamówienie z **pakowaniem na prezent**. Sklep pokazuje 48 produktów na stronę – prawie całą ofertę bez klikania.

Inspiracje: aniakruk.pl (jasność, prostota), bydziubeka.pl (kolor, materiały), bizuteriaparaiso.pl (zaufanie do materiału).
Do tego: zakupy wg koloru/kamienia/materiału, kamień urodzinowy, rozmiarówka, shop the look, prawdziwe opinie,
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
| Tło — porcelana | `#FBF8F5` | tło strony |
| Pudrowy róż | `#F5ECE7` | tła zdjęć, sekcje, pasek ogłoszeń |
| Tekst — ciepła czerń | `#2B2421` | tekst, przyciski, stopka |
| Tekst drugorzędny | `#6B5F58` | opisy, etykiety |
| Malina (akcent) | `#A3385A` | promocje, licznik koszyka, hover przycisków, słowo-klucz w nagłówku |
| Masło | `#F6EBC8` | etykieta „Nowość” |
| Linia | `#E9DED7` | obramowania |
| Nastroje: Radość / Energia / Czułość | `#F8E7B4` / `#F8D3C6` / `#F6DCE3` | pastelowe tła kart nastrojów |
| Nastroje: Spokój / Marzenia / Świeżość | `#CFE7E4` / `#E4DAF4` / `#E6EFC4` | jw. – tekst na nich zawsze ciemny |

**Czcionka**: *Figtree* 400–700 (jeden krój na całą stronę; nagłówki 500, przyciski 600).
Motyw ładuje ją **lokalnie** z `assets/fonts/` (RODO, CSP, 30 KB). W Elementorze: Ustawienia → Zaawansowane →
**Google Fonts: Wyłącz**, a w Globalnych czcionkach zostaw „Domyślne” — style nadaje motyw.

**Układ**: szerokość treści 1440 px, odstęp widżetów 16 px, punkty łamania 767 / 1024 px.

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
- **Atrybuty**: Produkty → Atrybuty → utwórz **Kolor** (slug `kolor`, typ „Kolor” w Variation Swatches – wtedy próbki biorą HEX z wtyczki) i **Materiał** (slug `material`) oraz **Kamień** (slug `kamien`, wartości np. Ametyst, Kwarc różowy, Turkus, Perła – slugi bez polskich znaków: `kwarc-rozowy`); zaznacz „Włącz archiwa”. Własny kolor próbki kamienia: HEX w wtyczce Variation Swatches albo meta `bd_color`. Przypisz je do produktów — z nich budują się sekcja „Szukaj po kolorze, kamieniu, materiale”, kropki na kartach i inteligentne wyszukiwanie. W sidebarze/szufladzie filtrów dodaj blok WooCommerce „Filtr atrybutu” dla Kamienia.
- **Kolor kolekcji w hero i kaflach**: w klasie kontenera zmień `bd-tint--lagoon` na `coral`, `lilac`, `lime`, `sun` lub `rose`.
- **Upsell**: w każdym produkcie uzupełnij *Dane produktu → Produkty powiązane*: **Dosprzedaż** = 2–3 produkty do kompletu (np. kolczyki do naszyjnika) → „Dobierz komplet”; **Sprzedaż krzyżowa** = drobne dodatki (np. łańcuszek, bransoletka do 79 zł) → „Pasuje do tego” w mini-koszyku i koszyku.
- **Nastroje**: przypisz produktom kolory (atrybut Kolor) albo tagi `nastroj-radosc`, `nastroj-energia`, `nastroj-czulosc`, `nastroj-spokoj`, `nastroj-marzenia`, `nastroj-swiezosc`.
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
| `[bd_usp variant="row\|list"]` | home, produkt | 4 argumenty: darmowa dostawa, nie ciemnieje, 30 dni na zwrot, pudełko (filtr `bydopamina_usp`) |
| `[bd_mood_picker images="radosc:ID,…"]` | home | **wyróżnik**: „Jak chcesz się dziś poczuć?” – 6 nastrojów = palety kolorów (atrybut `pa_kolor`); tag produktu `nastroj-radosc` itd. ma pierwszeństwo (ręczna selekcja); nastrój bez produktów się ukrywa (filtr `bydopamina_moods`) |
| `[bd_complete_set limit="3"]` | produkt | **upsell** „Dobierz komplet”: ten produkt + do 3 pasujących (z pola *Dosprzedaż*, a gdy puste – *Sprzedaż krzyżowa*), suma na żywo, jeden przycisk dodaje wszystko (AJAX) |
| `[bd_rating_summary_inline]` | hero | „★★★★★ 4,9/5 · 128 opinii klientek” – z prawdziwych opinii, pusty dopóki ich nie ma |
| `[bd_category_arches limit="6"]` | home | kategorie w łukach, kolejność z Produkty → Kategorie, zdjęcie = miniatura kategorii |
| `[bd_shop_by_color attribute="kolor"]` | home | próbki kolorów z atrybutu **pa_kolor** → sklep z filtrem `?filter_kolor=` (kolor z wtyczki Variation Swatches, meta `bd_color` lub wbudowanej mapy nazw) |
| `[bd_shop_by_material attribute="material"]` | home | chipsy materiałów z atrybutu **pa_material** (stal, ceramika, perły, muszle…) |
| `[bd_shop_by_stone attribute="kamien"]` | home | próbki kamieni z atrybutu **pa_kamien** (ametyst, turkus, perła, labradoryt… – 29 wbudowanych wyglądów) + **kamień urodzinowy** (miesiąc → kamienie tego miesiąca, filtr LUB). `birthstones="no"` ukrywa miesiące |
| `[bd_shop_by]` | home, sklep/kategoria | jeden przełącznik **Kolor / Kamień / Materiał** (pusta zakładka się ukrywa); na stronie kategorii filtruje w jej obrębie |
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

Automatycznie (bez shortcode'ów): **„Pasuje do tego” w mini-koszyku** (2 produkty ze Sprzedaży krzyżowej produktów w koszyku, dodawane jednym kliknięciem), szybkie „+” na kartach produktów, druga fotka z galerii po najechaniu na kartę produktu, **kropki dostępnych kolorów** pod ceną (produkty z wariantami), etykiety
„Nowość”/„Wyprzedane”, rabat „−20%”, **pakowanie na prezent** w checkoutcie (`BYDOPAMINA_GIFTWRAP_PRICE`),
przyklejony „Dodaj do koszyka” na telefonie, wyszukiwarka zwracająca produkty i **rozumiejąca kamienie, kolory i materiały**:
„naszyjnik z ametystem” → produkty z atrybutem Kamień = Ametyst i słowem „naszyjnik” w nazwie (działa też odmiana: ametystem, perłowy, turkusowe, ceramiczne).

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
