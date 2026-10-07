# AIOfficeLab / 01 FUNDAMENT – landing page

Adres: https://www.officeinfluencers.pl/ai-w-biurze

Plik do wklejenia: **`aiofficelab-fundament-elementor.html`**. To cały kod strony: treść, style, skrypt i dane strukturalne dla Google. Style działają tylko wewnątrz bloku `#aiol`, więc nie zmieniają nagłówka, stopki ani innych stron serwisu.

## Wklejenie do Elementora

1. WordPress → **Strony** → strona ze slugiem `ai-w-biurze` → **Edytuj w Elementorze**.
2. **Ustawienia strony** (ikona koła zębatego):
   - **Układ strony:** „Elementor – pełna szerokość”. Nagłówek i stopka serwisu zostają.
   - **Ukryj tytuł:** włącz. Nagłówek H1 jest już w kodzie.
3. Dodaj **kontener**:
   - Układ → **Szerokość treści: Pełna szerokość**,
   - Zaawansowane → **Dopełnienie (padding): 0** ze wszystkich stron, **odstęp (gap): 0**.
4. Przeciągnij do kontenera widżet **HTML**. Otwórz plik `aiofficelab-fundament-elementor.html`, zaznacz wszystko (Ctrl+A), skopiuj i wklej w pole „Kod HTML”.
5. **Opublikuj**. Sprawdź stronę w oknie prywatnym. Jeśli korzystasz z wtyczki cache, wyczyść cache.

> Wklejaj kod z konta **administratora**. Konta o niższych uprawnieniach usuwają z kodu `<style>` i `<script>`.

## Ustawienia (na samej górze kodu)

```js
window.AIOL_CONFIG = {
  signupUrl: "",        // link do formularza zapisów
  signupNewTab: false,  // true = formularz otwiera się w nowej karcie
  photoUrl: "",         // adres zdjęcia prowadzącej
  stickyBar: true       // pływający pasek z przyciskiem przy przewijaniu
};
```

- **signupUrl**: wklej link do formularza zapisów lub koszyka, np. `"https://www.officeinfluencers.pl/zapisy-fundament"`. Wszystkie 5 przycisków **„Zapisuję się”** zacznie prowadzić pod ten adres. Dopóki pole jest puste, przyciski przewijają stronę do sekcji z ceną.
- **photoUrl**: Media → Biblioteka → wybierz zdjęcie → „Kopiuj adres URL do schowka” i wklej między cudzysłowy. Najlepiej zdjęcie pionowe (4:5). Bez zdjęcia w tym miejscu wyświetlają się inicjały „AK”.
- **stickyBar**: `false` wyłącza pływający pasek z ceną i przyciskiem.

## Kolory

Kolory są zebrane na początku sekcji `<style>`:

| Zmienna | Do czego | Wartość |
|---|---|---|
| `--aiol-cta` | przyciski „Zapisuję się” (żółte) | `#f7c531` |
| `--aiol-cta-hover` | przycisk po najechaniu kursorem | `#eab308` |
| `--aiol-gold` | złoty akcent (jak na officeinfluencers.pl) | `#bf9a5a` |
| `--aiol-dark` | ciemne sekcje | `#191713` |

## SEO (Yoast / Rank Math)

- **Tytuł SEO:** `AI w biurze – szkolenie online LIVE | AIOfficeLab FUNDAMENT`
- **Opis meta:** `Szkolenie online LIVE (2 h): jak bezpiecznie i skutecznie korzystać z ChatGPT, Copilota, Claude i Gemini w pracy biurowej. 290 zł netto + VAT.`
- **Fraza kluczowa:** `AI w biurze`
- Dane strukturalne **Course** i **FAQPage** są już w kodzie (blok `application/ld+json`). Nie dodawaj osobnego schematu FAQ we wtyczce, bo powstanie duplikat.
- Po zmianie pytań lub odpowiedzi w FAQ popraw ten sam tekst w bloku `application/ld+json` na dole kodu.

## Analityka (opcjonalnie)

Po kliknięciu „Zapisuję się” do `dataLayer` (Google Tag Manager) trafia zdarzenie `aiol_cta_click` z parametrem `cta_location`. Wartości: `hero`, `efekty`, `cena`, `final`, `pasek`. Dzięki temu w GA4 widać, który przycisk działa najlepiej.

## Gdyby coś nie działało

- **Strona jest wąska albo ma ramki po bokach:** kontener nie ma pełnej szerokości albo ma padding (krok 3).
- **Przyciski nie prowadzą do formularza:** sprawdź `signupUrl`. Jeśli używasz optymalizacji JavaScriptu (WP Rocket „Opóźnij JavaScript”, LiteSpeed Cache, Autoptimize), dodaj do wyjątków `AIOL_CONFIG` oraz `aiol`.
- **Po kliknięciu sekcja chowa się pod przyklejonym nagłówkiem:** zwiększ `scroll-margin-top: 100px` przy `.aiol-sec`.
- **Fonty:** kod ładuje Inter i Playfair Display z Google Fonts. Jeśli serwis ładuje te fonty lokalnie (Elementor → Ustawienia → Wydajność), możesz usunąć 3 linie `<link …fonts.googleapis.com…>` z początku kodu.
