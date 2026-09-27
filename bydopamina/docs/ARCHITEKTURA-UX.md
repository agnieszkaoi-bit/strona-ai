# Architektura informacji i UX — bydopamina.pl

## 1. Założenia

1. **Mobile-first.** 70–80% ruchu e-commerce w PL to telefony. Każdy układ projektowany od 360 px w górę.
2. **Jedna główna akcja na ekran.** Czarny przycisk = główne CTA, obrys = drugorzędne. Nigdy dwa czarne obok siebie.
3. **Spokojna baza, dopaminowe akcenty.** 90% neutralnej, ciepłej bieli i czerni; róż `#FF4F8B` i limonka `#D9FF50` tylko do wyróżnień (promocja, nowość, postęp, hover). Kolor akcentu nigdy nie jest kolorem tekstu na jasnym tle — zawsze tłem pod czarnym tekstem (kontrast 6,6:1).
4. **Mniej kliknięć do zakupu.** Szybkie „Dodaj” na karcie, off-canvas mini-koszyk zamiast przeładowania, checkout na 1 stronie, płatności ekspresowe (BLIK, Apple/Google Pay).
5. **Zaufanie przed ceną.** Dostawa, zwroty i płatności widoczne w headerze, na karcie produktu i w koszyku — nie dopiero w regulaminie.
6. **Dostępność = konwersja.** WCAG 2.2 AA jest obowiązkowy dla sklepów od 28.06.2025 (European Accessibility Act / ustawa o dostępności). Cel dotyku min. 48 px, focus widoczny, `prefers-reduced-motion`, semantyczne nagłówki, etykiety pól.

## 2. Mapa strony

```
Start (/)
├── Sklep (/sklep/)                       ← archiwum: chipsy kategorii, sortowanie, 24 produkty/strona
│   ├── Kategoria A (/kategoria-produktu/a/)
│   ├── Kategoria B …
│   ├── Nowości (/sklep/?orderby=date)
│   ├── Bestsellery (/sklep/?orderby=popularity)
│   └── Promocje (/promocje/)             ← widget Produkty: źródło „Promocje”
├── Produkt (/produkt/nazwa/)
├── Koszyk (/koszyk/) → Zamówienie (/zamowienie/) → Dziękujemy
├── Moje konto (/moje-konto/)             ← zamówienia, zwroty, adresy, dane
├── O nas (/o-nas/)
├── Kontakt (/kontakt/)
├── FAQ (/faq/)
├── Dostawa i płatności (/dostawa-i-platnosci/)
├── Zwroty i reklamacje (/zwroty/)
└── Prawne: Regulamin · Polityka prywatności · Polityka cookies · Odstąpienie od umowy (formularz)
```

Menu główne (desktop): **Nowości · Kategorie (maks. 5) · Bestsellery · O nas**.
Mobile: hamburger (header) + **dolny pasek**: Start · Sklep · Szukaj · Konto · Koszyk.

## 3. Układy

### Header (sticky, szkło/blur, 68 px)
```
┌──────────────────────────────────────────────────────────────┐
│  Darmowa dostawa od 199 zł · Wysyłka w 24 h · 30 dni zwrotu   │  ← pasek ogłoszeń (czarny)
├──────────────────────────────────────────────────────────────┤
│ bydopamina     Nowości  Kategorie  Bestsellery  O nas   🔍 👤 👜│
└──────────────────────────────────────────────────────────────┘
Mobile:  ☰  bydopamina                                    🔍 👤 👜
```

### Strona główna — kolejność sekcji (lejek: inspiracja → wybór → zaufanie → zapis)
| # | Sekcja | Cel | Uwagi UX |
|---|---|---|---|
| 1 | **Hero split** — nagłówek 6,5 rem, 2 CTA, social proof, zdjęcie 4:5 | wartość marki w 3 s | zdjęcie = LCP, `fetchpriority=high`, bez karuzeli (karuzele hero obniżają konwersję) |
| 2 | **Pasek zaufania** — 4 ikony | usunięcie obaw | dostawa, zwroty, BLIK/PayPo, bezpieczeństwo |
| 3 | **Kategorie bento** — 1 duży + 3 kafle | szybka nawigacja | cały kafel klikalny, 2 kafle w kolorach akcentu |
| 4 | **Bestsellery** — 4 produkty | dowód społeczny | na mobile przewijana karuzela ze snapem (68% szerokości = widać, że jest więcej) |
| 5 | **Marquee wartości** | rytm, osobowość | pauza na hover, wyłączony przy `reduced-motion`, treść dla czytników |
| 6 | **Historia marki** — zdjęcie + 3 punkty | emocja, zaufanie | ludzie kupują od ludzi |
| 7 | **Nowości** | powrót stałych klientów | |
| 8 | **Opinie** — 3 karty + informacja o weryfikacji | dowód społeczny | wymóg Omnibus: informacja, czy i jak weryfikujesz opinie |
| 9 | **Newsletter −10%** — ciemny blok | zapis (retencja) | e-mail + zgoda + honeypot, double opt-in |
| 10 | **FAQ** — 4 pytania, natywne `<details>` | obiekcje | dane strukturalne FAQ generuje wtyczka SEO |

### Karta produktu
```
Desktop                                          Mobile
┌─────────────────────────┬──────────────────┐   ┌──────────────────┐
│                         │ Sklep › Kategoria│   │  Galeria (swipe) │
│   Galeria 4:5           │ Nazwa produktu H1│   │  ● ○ ○ ○         │
│   (miniatury pod)       │ ★★★★★ (128)      │   ├──────────────────┤
│                         │ 129 zł  (159 zł) │   │ Nazwa, cena      │
│                         │ Najniższa cena…  │   │ Warianty-pigułki │
│                         │ Krótki opis      │   │ [ Dodaj do koszyka]│
│                         │ Warianty         │   │ ● Wyślemy dziś   │
│                         │ [-1+] [DODAJ   ] │   │ ▓▓▓░ do darm. dost│
│                         │ ● Wyślemy dziś   │   │ Zaufanie 2×2     │
│                         │ ▓▓▓▓░ darmowa dost│  │ ▸ Opis            │
│                         │ Zaufanie 2×2     │   │ ▸ Szczegóły       │
│                         │ ▸ Opis ▸ Szczegóły│  │ ▸ Dostawa/zwroty  │
│                         │ ▸ Dostawa ▸ GPSR │   │ ▸ Bezpieczeństwo  │
│                         │ ▸ Opinie         │   │ ▸ Opinie          │
└─────────────────────────┴──────────────────┘   ├──────────────────┤
Kolumna prawa „przykleja się” przy przewijaniu     │▐ Nazwa 129 zł [Dodaj]▌│ ← sticky ATC
Pasuje do tego (upsell) · Może Ci się spodobać     │ ⌂  ▦  🔍  👤  👜  │ ← dolny pasek
                                                   └──────────────────┘
```
Decyzje: akordeon zamiast zakładek (lepszy na mobile, mniej przewijania), ETA wysyłki z odliczaniem
(pilność bez fałszywych liczników), sekcja **Bezpieczeństwo produktu** — wymóg rozporządzenia GPSR (UE 2023/988):
dane producenta i ostrzeżenia przy ofercie online.

### Sklep / kategoria
Nagłówek + opis kategorii (SEO) → chipsy kategorii (przewijane) → liczba wyników + sortowanie → siatka 4/3/2 kolumny → paginacja.
Filtry (rozmiar, kolor, cena) — gdy asortyment > 60 produktów: blok *Filtry produktów* WooCommerce lub
*Loop Grid + Taxonomy Filter* Elementora; na mobile w szufladzie od dołu.

### Koszyk → Zamówienie
- Kroki: **Koszyk → Dane i dostawa → Płatność** (orientacja, mniejszy lęk).
- Pasek darmowej dostawy nad listą, auto-aktualizacja ilości (bez przycisku „Aktualizuj koszyk”).
- Checkout na jednej stronie, e-mail jako pierwsze pole (odzyskiwanie porzuconych koszyków), zakup bez rejestracji,
  `inputmode` dla telefonu i kodu, pola 16 px (brak zoomu na iOS), ukryty „Adres 2”, firma opcjonalna.
- Przycisk: **„Zamawiam i płacę”** (wymóg art. 17 ustawy o prawach konsumenta — „z obowiązkiem zapłaty”).
- Prawa kolumna (podsumowanie) przyklejona; płatności jako duże kafle, zaznaczony = obramowanie.

## 4. Trendy 2026/2027 zastosowane w szablonie

| Trend | Realizacja |
|---|---|
| „Quiet UI” + akcent emocjonalny | ciepła biel, czerń, 2 akcenty; brak cieni i gradientów poza detalami |
| Ekspresyjna typografia | *Bricolage Grotesque*, hero do 104 px, ciasny tracking, `text-wrap: balance` |
| Bento grid | kategorie w siatce kafli o różnej wielkości |
| Strefa kciuka | dolny pasek nawigacji + sticky „Dodaj do koszyka” nad nim |
| Mikrointerakcje zamiast animacji | hover przycisku (akcent), pulsująca kropka ETA, płynny pasek postępu, scroll-driven reveal (CSS `animation-timeline`) |
| Commerce bez tarcia | off-canvas koszyk, szybkie „Dodaj”, BLIK/Apple Pay/PayPo, checkout 1 strona |
| Transparentność | ETA wysyłki, najniższa cena 30 dni, zweryfikowane opinie, dane producenta |
| Dostępność jako standard | WCAG 2.2 AA, EAA, focus ring, reduced motion |
| Gotowość na agentów AI / asystentów zakupowych | czysta semantyka, dane strukturalne Product/Offer (wtyczka SEO), pełne atrybuty produktów, `robots.txt` bez blokady crawlerów wyszukiwania AI |
| Wydajność = UX | brak jQuery w motywie, ikony SVG inline, fonty lokalnie, `fetchpriority` LCP; cel: LCP < 2,5 s, INP < 200 ms, CLS < 0,1 |

## 5. Wymogi prawne widoczne w UI (PL/UE, stan 2026)
- **Omnibus** — przy obniżce najniższa cena z 30 dni (wtyczka).
- **Opinie** — informacja, czy i jak są weryfikowane (sekcja opinii).
- **GPSR** — producent / podmiot odpowiedzialny + ostrzeżenia przy produkcie.
- **EAA** — dostępność cyfrowa sklepu i procesu zakupu + deklaracja dostępności.
- **Przycisk zamówienia** — „Zamawiam i płacę”.
- **Cookies** — baner z równorzędnymi przyciskami „Akceptuj” / „Odrzuć”, Consent Mode v2.
- **Prawo odstąpienia** — 14 dni ustawowo (szablon komunikuje 30 dni — ustaw w regulaminie).
- **Funkcja odstąpienia od umowy** (dyrektywa 2023/2673, od 19.06.2026) — łatwo dostępny, wyraźnie oznaczony przycisk/formularz odstąpienia online; w szablonie link „Odstąpienie od umowy” w stopce + w *Moim koncie* — podłącz formularz (np. Elementor Form lub wtyczka zwrotów).
