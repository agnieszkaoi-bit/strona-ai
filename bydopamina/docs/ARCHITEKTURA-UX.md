# Architektura informacji i UX — bydopamina.pl (sklep z biżuterią)

## 1. Kierunek: editorial, nie „szablon AI”

Sklepy z biżuterią ze stali wyglądają dziś bardzo podobnie: biały ekran,
karuzela banerów, siatka produktów, różowe przyciski. Generowane szablony dokładają do tego swoje tiki:
kafle bento w pastelach, gradientowe podkreślenia, kropki nad nagłówkami, pulsujące emoji, trzy identyczne
karty opinii z wymyślonymi imionami. bydopamina idzie w przeciwną stronę — **jak magazyn modowy**:

| Zamiast | Robimy |
|---|---|
| karuzela banerów w hero | jedno duże zdjęcie + drugie mniejsze, tytuł 140 px w szeryfie z kursywą |
| „eyebrow” z kropką nad H2 | numerowane sekcje `01 · Tytuł · link →` oddzielone cienką linią |
| kolorowe kafle kategorii | zdjęcia w **łukach** (kształt biżuteryjnej gabloty) z numerami w mono |
| zaokrąglone „pigułki” wszędzie | kąty proste 2 px, cienkie linie, przyciski prostokątne |
| 5 kolorów akcentu | kolor robi fotografia; interfejs espresso + kość słoniowa, jeden akcent: **wiśnia** |
| ikony w kółkach | liczby jako grafika: **316L · 18K · 0** (niklu) |
| fade-in wszystkiego | zdjęcia „odsłaniają się” jak kurtyna (CSS scroll-driven), reszta stoi w miejscu |
| wymyślone opinie | shortcode pobiera **prawdziwe**, zweryfikowane opinie i liczy średnią |
| „dopaminowe” kolory rozlane po całym UI | **color-blocking kolekcji**: kolor jest tłem zdjęcia/kolekcji, interfejs zostaje neutralny |

### Dwie inspiracje, jedna marka
- **bizuteriaparaiso.pl** → zaufanie do materiału: stal 316L, złoto 18K, wodoodporność, hipoalergiczność.
- **bydziubeka.pl** → kolor i zabawa: bogata paleta, mieszanie materiałów (stal, ceramika, muszle, perły, drewno, skóra), kolekcje co sezon.
- **bydopamina** łączy oba: spokojny, redakcyjny interfejs + każda kolekcja ma własny kolor (Coral, Lagoon, Lilac…),
  a klient może kupować **wg koloru** i **wg materiału** — tak, jak myśli o biżuterii modowej („coś turkusowego do sukienki”).

Typografia: *Instrument Serif* (nagłówki, kursywa na słowie-kluczu), *Geist* (tekst), *Geist Mono*
(etykiety, liczby porządkowe, meta). Trzy kroje, jedna rodzina decyzji.

## 2. Mapa strony

```
Start (/)
├── Sklep (/sklep/)                           siatka 4/3/2, chipsy podkategorii, sortowanie
│   ├── Naszyjniki  → Łańcuszki · Z zawieszką · Choker · Z perłą
│   ├── Kolczyki    → Kółka · Wkręty · Nausznice · Wiszące
│   ├── Pierścionki → Obrączki · Sygnety · Z kamieniem · Regulowane
│   ├── Bransoletki → Łańcuszkowe · Sztywne · Na kostkę
│   ├── Zestawy
│   └── Prezenty    → do 79 / 129 / 199 zł (filtr ?max_price=)
├── Kolekcje (/kolekcja/solstice/, /layering/, /minimal/)   tag lub kategoria „kolekcja”
├── Produkt (/produkt/nazwa/)
├── Ulubione (/lista-zyczen/)
├── Koszyk → Zamówienie → Dziękujemy
├── Moje konto (zamówienia, zwroty, adresy)
├── O materiale (/o-materiale/)               dlaczego 316L + PVD – najczęstsze pytanie klientów
├── Pielęgnacja · Rozmiarówka · Dostawa i płatności · Zwroty · FAQ · Kontakt
└── Prawne: Regulamin · Prywatność · Cookies · Odstąpienie od umowy · Deklaracja dostępności
```

**Menu desktop:** Nowości · Naszyjniki · Kolczyki · Pierścionki · Bransoletki · Prezenty — logo na środku.
**Mobile:** hamburger + logo + szukaj + koszyk; **dolny pasek**: Start · Sklep · Szukaj · Ulubione · Koszyk.

## 3. Strona główna — kolejność i cel sekcji

| # | Sekcja | Cel | Decyzja UX |
|---|---|---|---|
| — | Pasek ogłoszeń (mono, ciemny) | usunąć obawy od pierwszej sekundy | na telefonie 1 komunikat, na desktopie 4 |
| — | **Hero** — tytuł na całą szerokość, zdjęcie 5:4 na tle koloru kolekcji (8/12) + zdjęcie 1:1 i tekst (4/12) | emocja + jasna oferta | 1 CTA pełne („Kup kolekcję”) + 1 link; meta: materiał / wysyłka / zwrot |
| — | **Cechy materiału** (4 kolumny, linie) | odpowiedź na pytanie nr 1: „czy to się nie ściera?” | ikony w kolorze złota, bez teł |
| 01 | **Kategorie w łukach** | nawigacja kciukiem | 6 kategorii, na mobile przewijane (widać 2,5 – sygnał, że jest więcej) |
| 02 | **Wybierz kolor / materiał** | skrót dla klientki, która szuka „czegoś koralowego” | duże próbki z liczbą produktów + chipsy materiałów; linki do filtrów WooCommerce |
| 03 | **Zakładki produktów** | wybór bez przewijania 3 sekcji | Bestsellery / Nowości / Promocje jako duże słowa w szeryfie; ARIA tabs |
| 04 | **O materiale** (ciemna sekcja) | zaufanie, uzasadnienie ceny | liczby 316L · 18K · 0 zamiast ikon |
| 05 | **Shop the look** | średnia wartość koszyka ↑ | punkty na zdjęciu ↔ lista produktów; na dotyku 1. tap podświetla, 2. przenosi |
| 06 | **Kolekcje sezonu** (3 kafle w kolorach kolekcji) | inspiracja, powroty co sezon | color-blocking; podpis pod zdjęciem, nie na nim (czytelność) |
| 07 | **Prezenty** (piaskowe tło) | ruch sezonowy (święta, Walentynki, Dzień Matki) | wybór wg budżetu + informacja o pakowaniu |
| 08 | **Opinie** | dowód społeczny | średnia liczona automatycznie, tylko zweryfikowane zakupy, miniatura produktu przy opinii |
| — | **Newsletter** — jedna linia | retencja | e-mail + zgoda + honeypot, bez wyskakującego okna na wejściu |

## 4. Karta produktu

```
Desktop                                                   Mobile
┌──────────────────────────────┬─────────────────────┐    ┌──────────────────┐
│ Zdjęcie na modelce 5:4       │ SKLEP / NASZYJNIKI  │    │ Galeria – swipe  │
│                              │ [NOWOŚĆ][18K][316L] │    │ (88% szer., widać │
│                              │ Naszyjnik Aura      │    │  kolejne zdjęcie) │
│                              │ ★★★★★ 128 opinii     │    ├──────────────────┤
│                              │ 119 zł  (149 zł)    │    │ Etykiety, nazwa  │
│                              │ Najniższa z 30 dni… │    │ Cena + Omnibus   │
├──────────────┬───────────────┤ Kolor: ◐ Złoty ○ Srebrny│  │ Kolor, długość   │
│ Packshot 4:5 │ Detal 4:5     │ Długość  [40][45][50]│    │ ▭ Rozmiarówka    │
│              │               │           ▭ Rozmiarówka │ │ [1][DODAJ · 119] │
└──────────────┴───────────────┤ [1] [ DODAJ · 119 zł ]│   │ ● wyślemy dziś   │
                               │ ● Zamów w 3 h – dziś │    │ ── do darmowej   │
     kolumna prawa przyklejona │ ───── do darmowej dost.│  │ Cechy 2×2        │
     podczas przewijania       │ Cechy materiału 2×2  │    │ + Opis           │
                               │ + Opis               │    │ + Materiał…      │
                               │ + Materiał i pielęgn.│    │ + Opinie         │
                               │ + Wymiary · Dostawa  │    ├──────────────────┤
                               │ + Bezpieczeństwo · Opinie│ │ Aura 119 zł [DODAJ]│ ← sticky
                               └─────────────────────┘    │ ⌂  ▦  ⌕  ♡  ▢   │ ← dolny pasek
01 Noś razem (upsell)  ·  02 Może Ci się spodobać          └──────────────────┘
```

- **Rozmiarówka** w panelu bocznym (`<dialog>`, na telefonie od dołu): tabela PL (obwód − 40 = rozmiar),
  instrukcja pomiaru, długości łańcuszków z opisem, gdzie leżą. Główna przyczyna zwrotów pierścionków.
- **Warianty jako przyciski** (Variation Swatches): kolor złoty/srebrny z próbką, niedostępne przekreślone.
- **Etykiety materiału** w mono pod okruszkami — odpowiedź zanim klient zada pytanie.
- **„Noś razem”** = upsell ustawiony ręcznie w produkcie (Produkty → Dane → Powiązane).
- **Bezpieczeństwo produktu** (GPSR) — producent, podmiot w UE, ostrzeżenia (małe elementy).

## 5. Sklep / kategoria
Okruszki → duży tytuł kategorii + opis (SEO) → chipsy podkategorii z liczbą produktów → wyniki + sortowanie →
siatka 4/3/2 → paginacja numerowana (mono). Karta produktu: packshot 4:5, **na hover zdjęcie na modelce**,
„Szybko dodaj” na dole zdjęcia (desktop), etykiety Nowość / −20% / Wyprzedane, **kropki dostępnych kolorów** pod ceną.
Filtry (kolor, materiał, długość, cena) — przy > 60 produktach: blok „Filtry produktów” WooCommerce w szufladzie.

## 6. Koszyk i zamówienie
- Kroki w mono: **01 Koszyk — 02 Dane i dostawa — 03 Płatność**.
- Pasek do darmowej dostawy (cienka linia), automatyczne przeliczanie ilości.
- **Pakowanie na prezent** (checkbox + opłata, zapis w zamówieniu jako `_bd_giftwrap`) — biżuterię często kupuje się na prezent.
- E-mail jako pierwsze pole, zakup bez konta, `inputmode` dla telefonu i kodu, pola 16 px (bez zoomu iOS).
- Metody płatności jako lista z obramowaniem; zaznaczona — ciemna ramka.
- Przycisk **„Zamawiam i płacę”** (art. 17 ustawy o prawach konsumenta).

## 7. Trendy 2026/27 w tym szablonie
| Trend | Realizacja |
|---|---|
| Editorial commerce | siatka 12 kolumn, asymetria 8/4, duży szeryf, numerowane sekcje |
| „Quiet UI + color-blocking” | neutralny interfejs, kolor w blokach kolekcji, cienkie linie, brak cieni |
| Shop by color | próbki kolorów i materiałów jako główna nawigacja obok kategorii |
| Typografia jako grafika | liczby 316L/18K/0, tytuł hero 140 px, kursywa na słowie-kluczu |
| Shoppable content | shop the look z punktami, kolekcje jako historie |
| Strefa kciuka | dolny pasek, sticky dodaj do koszyka, panele od dołu |
| Scroll-driven motion (CSS) | odsłanianie zdjęć bez JS, wyłączone przy `prefers-reduced-motion` |
| Przejrzystość | etykiety materiału, ETA wysyłki, Omnibus, tylko zweryfikowane opinie |
| Gotowość na asystentów zakupowych AI | semantyczny HTML, dane strukturalne Product/Offer, pełne atrybuty (materiał, długość, rozmiar) |
| Dostępność jako standard | WCAG 2.2 AA, kontrasty 6,5–15,7:1, focus, ARIA tabs, `<dialog>` |
| Wydajność | fonty lokalnie (150 KB), ikony SVG inline, brak jQuery w motywie; cel: LCP < 2,5 s, INP < 200 ms |

## 8. Wymogi prawne widoczne w UI (PL/UE, 2026)
- **Omnibus** — najniższa cena z 30 dni przy promocji (wtyczka; style gotowe).
- **Opinie** — informacja o weryfikacji pod sekcją opinii.
- **GPSR** — dane producenta i ostrzeżenia w karcie produktu.
- **EAA** — dostępność sklepu + deklaracja dostępności (link w stopce).
- **Funkcja odstąpienia od umowy** (dyrektywa 2023/2673, od 19.06.2026) — link w stopce i w *Moim koncie*.
- **Cookies** — baner z równorzędnymi „Akceptuj” / „Odrzuć”, Consent Mode v2.
