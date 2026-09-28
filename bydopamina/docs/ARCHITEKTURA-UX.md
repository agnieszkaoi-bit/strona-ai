# Architektura informacji i UX — bydopamina.pl (kolorowa biżuteria, ~50 produktów na start)

## 1. Założenia

1. **Stonowana strona, kolorowa biżuteria.** Porcelanowa biel, pudrowy róż, ciepła czerń. Kolor pokazują zdjęcia
   produktów i pastelowe tła nastrojów – interfejs nie konkuruje z biżuterią.
2. **Klasyczny układ sklepu jubilerskiego.** Slider na całą szerokość, menu wielkimi literami, prostokątne przyciski, prawie proste rogi zdjęć. Bez „pigułek”, kolorowych słów w nagłówkach, małych etykiet nad tytułami i pływających dymków – czyli bez typowych cech szablonów generowanych przez AI.
3. **Jeden czytelny krój.** *Figtree*, tekst 17 px, nagłówki 500. Zero kroju ozdobnego – czytelność na telefonie jest ważniejsza.
4. **Najkrótsza droga do koszyka.** Każdy ekran ma jedną główną akcję (czarna pigułka). „+” na karcie produktu
   dodaje do koszyka bez wchodzenia w produkt.
5. **Upsell w trzech momentach**, nigdy nachalnie (bez wyskakujących okien): na karcie produktu („Dobierz komplet”),
   po dodaniu do koszyka (mini-koszyk: „Pasuje do tego” + pasek do darmowej dostawy), w koszyku (sprzedaż krzyżowa)
   i przy zamówieniu (pakowanie na prezent).
6. **Dostępność = konwersja.** WCAG 2.2 AA (EAA od 28.06.2025): kontrast tekstu ≥ 6:1, cel dotyku 44–48 px, fokus, reduced motion.

## 2. Wyróżniki marki

| Wyróżnik | Gdzie | Dlaczego |
|---|---|---|
| **„Jak chcesz się dziś poczuć?”** | strona główna | nazwa *dopamina* → biżuteria dobierana do nastroju. Radość (złoto, żółć), Energia (koral), Czułość (róż, perła), Spokój (turkus, mięta), Marzenia (lila), Świeżość (zieleń). Klientka wybiera emocję zamiast przeglądać kategorie – szczególnie przy kolorowej ofercie |
| **Logo z malinową kropką** `bydopamina.` | header + wielki napis w stopce | minimalny znak, łatwy do powtórzenia na opakowaniach i w social media |
| **Słowo-klucz w malinie** | nagłówki („Biżuteria, która **poprawia humor**”) | jeden akcent koloru w typografii zamiast grafik |

## 3. Mapa strony (start: ~50 produktów)

```
Start (/)
├── Sklep (/sklep/)  – 48 produktów na stronę = prawie cała oferta bez paginacji
│   ├── Kolczyki · Naszyjniki · Bransoletki · Pierścionki · Zestawy
│   └── Prezenty (filtr ceny: do 79 / 129 / 199 zł)
├── Produkt (/produkt/…/)
├── Ulubione · Koszyk → Zamówienie → Dziękujemy · Moje konto
├── O nas · Kontakt · FAQ · Dostawa i płatności · Zwroty · Rozmiarówka · Pielęgnacja
└── Prawne: Regulamin · Prywatność · Cookies · Odstąpienie od umowy · Deklaracja dostępności
```
Header (klasyczny układ sklepu jubilerskiego): **pasek promocji** (malina) → **pasek informacyjny** (dostawa, zwroty, pudełko | kontakt, karty podarunkowe) →
**logo po lewej · menu WIELKIMI LITERAMI: Nowości · Biżuteria · Bestsellery · Na prezent · Promocje · pole „Szukaj” · Profil / Ulubione / Koszyk z podpisami**.
Telefon: hamburger + logo + szukaj + koszyk; **dolny pasek**: Start · Sklep · Szukaj · Ulubione · Koszyk.
Przy 50 produktach nie ma sensu rozbudowane menu z podkategoriami – kategorie główne wystarczą.

## 4. Strona główna – kolejność (8 sekcji)

| # | Sekcja | Cel | Decyzja UX |
|---|---|---|---|
| 1 | **Slider hero na całą szerokość** (2–3 slajdy) | oferta i akcja w 3 s | zdjęcie, DUŻY napis, podtytuł, prostokątny przycisk „Sprawdź”; strzałki + kropki, autoplay 6 s z pauzą po najechaniu; na telefonie kadr 4:5, napis na dole |
| 2 | **Kategorie** | nawigacja | kafelki zdjęć 3:4 z nazwą WIELKIMI LITERAMI |
| 3 | **Polecamy** (zakładki Bestsellery / Nowości / Promocje) | szybki zakup | „+” na karcie = do koszyka bez przeładowania; kropki kolorów wariantów |
| 4 | **Biżuteria na nastrój** | wyróżnik + wybór z kolorowej oferty | 6 kart w pastelach; nastrój bez produktów się ukrywa |
| 5 | **Noś razem** (shop the look) | wyższa wartość koszyka | punkty na zdjęciu ↔ lista produktów |
| 6 | **Na prezent** | ruch prezentowy | budżet do 79 / 129 / 199 zł + informacja o pakowaniu |
| 7 | **Opinie klientek** | dowód społeczny | tylko zweryfikowane zakupy, średnia liczona automatycznie |
| 8 | **−10% na pierwsze zakupy** | zapis do newslettera | spokojny blok na końcu strony zamiast wyskakującego okna |

Argumenty zaufania (dostawa, zwroty, pudełko) są w pasku informacyjnym nad headerem – na każdej stronie, bez osobnej sekcji.

## 5. Karta produktu – ścieżka i upsell

```
Galeria (zdjęcie na modelce + packshoty)  │  Sklep / Naszyjniki
                                          │  [Nowość] [18K złoto] [Stal 316L]
                                          │  Naszyjnik Aura            ★★★★★ 128
                                          │  119 zł (149 zł) · najniższa z 30 dni
                                          │  Kolor: (●) Złoty ( ) Srebrny
                                          │  Długość: [40] [45] [50]   ▭ Rozmiarówka
                                          │  [1] [ Dodaj do koszyka · 119 zł ]
                                          │  ┌ Dobierz komplet ─────────────── ┐
                                          │  │ ☑ Naszyjnik Aura (ten)   119 zł │
                                          │  │ ☑ Kolczyki Aura mini      69 zł │
                                          │  │ ☑ Bransoletka Aura        79 zł │
                                          │  │ Razem: 267 zł [Dodaj komplet]    │
                                          │  └──────────────────────────────── ┘
                                          │  ● Zamów w ciągu 3 h – wyślemy dziś
                                          │  ▓▓▓▓░░ Brakuje 80 zł do darmowej dostawy
                                          │  4 argumenty · Opis · Materiał · Wymiary · Dostawa · GPSR · Opinie
Może Ci się spodobać (podobne)
```
- **Dobierz komplet** – produkty z pola *Dosprzedaż*; suma liczy się na żywo; jeden przycisk dodaje zaznaczone (AJAX).
  Przy produkcie z wariantami lista zawiera tylko dodatki („Dodaj zaznaczone”), a sam produkt dodaje się formularzem wyżej.
- **Pasek do darmowej dostawy** pod przyciskiem – naturalny powód, żeby dobrać drugi produkt.
- Telefon: przyklejony pasek „Nazwa · cena · Dodaj do koszyka” nad dolną nawigacją.

## 6. Po dodaniu do koszyka
Mini-koszyk wysuwa się z boku (bez przeładowania): lista, **pasek do darmowej dostawy**, **„Pasuje do tego”**
(2 produkty ze *Sprzedaży krzyżowej*, dodawane „+”), przyciski „Zamówienie” (główny) i „Koszyk”.

## 7. Koszyk i zamówienie
Kroki ①②③ (Koszyk → Dane i dostawa → Płatność), automatyczne przeliczanie ilości, sprzedaż krzyżowa w koszyku,
zakup bez konta, e-mail jako pierwsze pole, BLIK / Apple Pay / PayPo, InPost, **pakowanie na prezent**
(checkbox + opłata, zapis w zamówieniu), przycisk „Zamawiam i płacę” (art. 17 ustawy o prawach konsumenta).

## 8. Sklep / kategoria
Okruszki → tytuł i krótki opis → chipsy kategorii → przełącznik **Kolor / Kamień / Materiał** (w obrębie kategorii) →
liczba wyników + sortowanie → siatka 4/3/2 (48 na stronę). Wyszukiwarka rozumie nazwy kamieni, kolorów i materiałów
także w odmianie („kolczyki z perłą”, „turkusowe”).

## 9. Wymogi prawne widoczne w UI (PL/UE, 2026)
- **Omnibus** – najniższa cena z 30 dni przy promocji (wtyczka; style gotowe).
- **Opinie** – informacja o weryfikacji pod sekcją opinii.
- **GPSR** – dane producenta i ostrzeżenia w karcie produktu.
- **EAA** – dostępność sklepu + deklaracja dostępności (link w stopce).
- **Funkcja odstąpienia od umowy** (dyrektywa 2023/2673, od 19.06.2026) – link w stopce i w *Moim koncie*.
- **Cookies** – baner z równorzędnymi „Akceptuj” / „Odrzuć”, Consent Mode v2.
