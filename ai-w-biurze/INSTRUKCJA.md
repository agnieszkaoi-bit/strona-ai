# AIOfficeLab / 01 FUNDAMENT – landing page z formularzem zgłoszeń

Adres: https://www.officeinfluencers.pl/ai-w-biurze

Wdrożenie ma dwie części:

| Co | Plik | Gdzie trafia |
|---|---|---|
| Strona | `aiofficelab-fundament-elementor.html` | widżet **HTML** w Elementorze |
| Wysyłka zgłoszeń | `formularz-aiol.zip` (folder `formularz-aiol/`) | serwer officeinfluencers.pl (Zenbox) |

Formularz wysyła zgłoszenia na **office@officeinfluencers.pl** przez ten sam sprawdzony skrypt co formularz szkolenia dla asystentek zarządu. Zmieniły się tylko pola i temat wiadomości.

## 1. Wgraj folder `formularz-aiol` na serwer

1. Zaloguj się do panelu Zenbox → **Menedżer plików**. Możesz też użyć FTP, np. FileZilla.
2. Otwórz główny katalog strony officeinfluencers.pl, czyli ten, w którym są `wp-config.php`, `wp-content` i `wp-admin`.
3. Wgraj `formularz-aiol.zip` i **rozpakuj**. Powstanie folder `formularz-aiol` z czterema plikami: `formularz.php`, `nip.php`, `bezpieczenstwo.php`, `.htaccess`.
4. Sprawdzenie: otwórz w przeglądarce `https://www.officeinfluencers.pl/formularz-aiol/formularz.php`. Prawidłowa odpowiedź to komunikat `{"ok":false,"komunikat":"Dozwolona jest tylko metoda POST."}`. Oznacza on, że skrypt działa.

> Folderu **nie nazywaj** `ai-w-biurze`. Zasłoniłby stronę WordPressa o tym adresie. Jeśli wybierzesz inną nazwę, popraw ustawienie `formularz` w kodzie strony.

## 2. Wklej stronę do Elementora

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

## 3. Wyślij zgłoszenie testowe

1. Na stronie kliknij „Zapisuję się” i wypełnij formularz swoimi danymi.
2. Po wysłaniu powinien pojawić się komunikat „Zgłoszenie przyjęte”.
3. Sprawdź skrzynkę **office@officeinfluencers.pl** i folder spam. Wiadomość ma temat „Zgłoszenie: AIOfficeLab FUNDAMENT (AI w biurze)”. Przycisk „Odpowiedz” trafia do osoby, która się zgłosiła.

## Ustawienia (na samej górze kodu strony)

```js
window.AIOL_CONFIG = {
  formularz: "/formularz-aiol/formularz.php", // skrypt wysyłający zgłoszenie
  platnosc: "https://easl.ink/XD2bl",         // link do płatności online
  termin: "czwartek, 5 listopada 2026",       // data (i godziny) edycji
  heroPhotoUrl: "",                           // inne zdjęcie w hero (puste = wbudowane)
  photoUrl: "",                               // inne zdjęcie w sekcji „Prowadząca” (puste = wbudowane)
  stickyBar: true                             // pływający pasek z przyciskiem
};
```

- **termin**: data edycji, teraz „czwartek, 5 listopada 2026”. Możesz dopisać godziny, np. „czwartek, 5 listopada 2026, 10:00-12:00”. Termin pojawia się w pasku na górze, w hero, w karcie z ceną, w finale, w pływającym pasku i w mailu ze zgłoszeniem. Puste pole = bez terminu.
  Po zmianie daty popraw też `"startDate": "2026-11-05"` w bloku `application/ld+json` na dole kodu (format rok-miesiąc-dzień).
- **Zdjęcia prowadzącej** są wbudowane w kod strony: portret w hero i zdjęcie z konferencji w sekcji „Prowadząca”. Nie trzeba ich nigdzie wgrywać.
- **heroPhotoUrl** / **photoUrl**: jeśli chcesz inne zdjęcie, wejdź w Media → Biblioteka → wybierz zdjęcie → „Kopiuj adres URL do schowka” i wklej między cudzysłowy. Najlepiej zdjęcie pionowe (4:5) z twarzą w górnej części kadru.
- **platnosc**: link do płatności online, otwierany przez przyciski „Kupuję i przechodzę do płatności”. Pusty cudzysłów `""` usuwa te przyciski ze strony, np. gdy sprzedaż online jest zamknięta.
- **stickyBar**: `false` wyłącza pływający pasek z ceną i przyciskiem.

## Dwa sposoby zapisu

1. **Formularz zgłoszenia.** Wszystkie żółte przyciski **„Zapisuję się”** przewijają stronę do formularza, a zgłoszenie trafia na office@officeinfluencers.pl.
2. **Płatność online.** Przyciski z cienkim obrysem **„Kupuję i przechodzę do płatności”** prowadzą do linku z ustawienia `platnosc`. Są w trzech miejscach: w hero obok „Zapisuję się”, w karcie z ceną nad formularzem i w sekcji końcowej.

Pływający pasek, pasek na górze i przyciski w środku strony prowadzą tylko do formularza.

## Jak działa formularz

- **Grupa docelowa:** szkolenie jest przeznaczone wyłącznie dla działów administracji biurowej. Mówią o tym hero, kafelek „Dla kogo?”, FAQ i podpowiedź przy polu „Stanowisko”.
- **Pola:** imię i nazwisko, e-mail, telefon, stanowisko, liczba osób (1–10), płatnik (firma albo osoba prywatna), NIP, nazwa firmy, adres do faktury, uwagi, zgoda RODO (wymagana) i zgoda marketingowa (dobrowolna).
- **NIP:** po wpisaniu 10 cyfr strona sama pobiera nazwę i adres firmy z wykazu podatników VAT Ministerstwa Finansów.
- **Podsumowanie:** w karcie z ceną obok formularza kwota liczy się na żywo (liczba osób × 290 zł netto). Ta sama kwota trafia do maila.
- **Kilka osób:** przy liczbie osób większej niż 1 podpowiedź w polu „Uwagi” prosi o dane pozostałych uczestników.
- **Ochrona przed spamem** (jak w poprzednim formularzu): ukryte pole-pułapka, odrzucanie zbyt szybkich wysyłek, limit wysyłek z jednego adresu IP, blokada powtarzanej treści, odrzucanie adresów jednorazowych i zmyślonych domen.
- **Bezpieczeństwo:** adres odbiorcy i temat są na sztywno w kodzie, a skrypt przyjmuje zgłoszenia tylko ze strony officeinfluencers.pl. Treść jest oczyszczona z ukrytych poleceń dla AI (prompt injection). Nic nie jest zapisywane na serwerze.

## Wygląd

Cała strona jest na białym tle i używa jednego prostego kroju: **Inter**. Treść jest ułożona w siatkę jasnych kafelków (białych, jasnoszarych i beżowych). Nagłówki są w średniej grubości, bez pogrubień, bez ikon, ozdobnych plam i świecących efektów. Jedyny mocny kolor to żółte przyciski „Zapisuję się”.

Kolejność sekcji: pasek z terminem → hero → klienci (przewijana taśma) → dla kogo i o szkoleniu → puenta „Nie jak napisać prompt” → opinie → program (4 moduły) → po szkoleniu i dalsza ścieżka → pytania z sali → prowadząca → cena z formularzem zgłoszenia → dla firm i kontakt → FAQ w dwóch kolumnach → finał.

### Animacje

- kafelki i nagłówki płynnie wjeżdżają, gdy pojawiają się na ekranie,
- żółte zakreślenie rysuje się pod kluczowymi słowami,
- nazwy klientów przewijają się w taśmie (zatrzymuje się po najechaniu kursorem),
- cienki złoty pasek na samej górze pokazuje, ile strony już przeczytano,
- przyciski i ramki kafelków delikatnie zmieniają kolor po najechaniu kursorem, a kwota w karcie z ceną „podskakuje” po zmianie liczby osób.

Jeśli ktoś ma w systemie włączone ograniczanie ruchu, strona wyświetla się bez animacji. Bez JavaScriptu cała treść też jest widoczna od razu.

### Kolory

| Zmienna | Do czego | Wartość |
|---|---|---|
| `--aiol-cta` | przyciski „Zapisuję się” (żółte) | `#f7c531` |
| `--aiol-cta-hover` | przycisk po najechaniu kursorem | `#efb918` |
| `--aiol-mark` | jasnożółte zakreślenie w nagłówkach | `#fbe39a` |
| `--aiol-gold` | złoty akcent (jak na officeinfluencers.pl) | `#ad8644` |
| `--aiol-line` | ramki białych kafelków i cienkie linie | `#ebebeb` |
| `--aiol-soft` | jasnoszare kafelki i tło pól formularza | `#f7f7f5` |
| `--aiol-gold-soft` | beżowe kafelki, pasek na górze, karta z ceną | `#f8f3ea` |
| `--aiol-ink` | nagłówki i ciemny tekst | `#1a1a1a` |

## SEO (Yoast / Rank Math)

- **Tytuł SEO:** `AI w biurze – szkolenie online LIVE | AIOfficeLab FUNDAMENT`
- **Opis meta:** `Szkolenie online LIVE (2 h) dla działów administracji biurowej, 5 listopada 2026: jak bezpiecznie i skutecznie korzystać z ChatGPT, Copilota, Claude i Gemini. 290 zł netto + VAT.`
- **Fraza kluczowa:** `AI w biurze`
- Dane strukturalne **Course** i **FAQPage** są już w kodzie (blok `application/ld+json`). Nie dodawaj osobnego schematu FAQ we wtyczce, bo powstanie duplikat.
- Po zmianie pytań lub odpowiedzi w FAQ popraw ten sam tekst w bloku `application/ld+json` na dole kodu.

## Analityka (opcjonalnie)

Do `dataLayer` (Google Tag Manager) trafiają trzy zdarzenia:

- `aiol_cta_click`: kliknięcie „Zapisuję się”, z parametrem `cta_location` (`pasek-gora`, `hero`, `opinie`, `program`, `efekty`, `final`, `pasek`),
- `aiol_platnosc_click`: kliknięcie „Kupuję i przechodzę do płatności”, z parametrem `cta_location` (`hero`, `cena`, `final`),
- `aiol_zgloszenie`: wysłane zgłoszenie, z parametrem `liczba_osob`. Ustaw je w GA4 jako konwersję.

## Gdyby coś nie działało

- **Po kliknięciu „Zapisuję się” w formularzu pojawia się „Nie udało się wysłać zgłoszenia”:** sprawdź krok 1.4. Adres skryptu musi zgadzać się z ustawieniem `formularz`.
- **Pojawia się „Żądanie spoza strony officeinfluencers.pl”:** strona i folder `formularz-aiol` muszą być w tej samej domenie (`www.officeinfluencers.pl`). Ustawienie `formularz` zaczyna się od `/`, bez `https://`.
- **Formularz pokazuje „Zgłoszenie przyjęte”, ale mail nie dochodzi:** sprawdź spam. Następnie w panelu Zenbox sprawdź, czy istnieje skrzynka office@officeinfluencers.pl i czy rekord SPF domeny obejmuje serwer Zenbox.
- **NIP nie uzupełnia danych:** rejestr Ministerstwa Finansów bywa chwilowo niedostępny. Wtedy nazwę i adres wpisuje się ręcznie, a zgłoszenie i tak dochodzi.
- **Strona jest wąska albo ma ramki po bokach:** kontener nie ma pełnej szerokości albo ma padding (krok 2.3).
- **Przyciski nie przewijają do formularza albo kafelki pojawiają się dopiero po ruchu myszką** (WP Rocket „Opóźnij JavaScript”, LiteSpeed Cache, Autoptimize): dodaj do wyjątków `AIOL_CONFIG` oraz `aiol`.
- **Po kliknięciu sekcja chowa się pod przyklejonym nagłówkiem:** zwiększ wartość `scroll-margin-top: 100px` (występuje w kodzie 3 razy).
- **Fonty:** kod ładuje Inter z Google Fonts. Jeśli serwis ładuje go lokalnie (Elementor → Ustawienia → Wydajność), możesz usunąć 3 linie `<link …fonts.googleapis.com…>` z początku kodu.

## Pliki w repozytorium

- `aiofficelab-fundament-elementor.html`: kod strony do Elementora,
- `formularz-aiol.zip`: paczka do wgrania na serwer (ta sama zawartość co `serwer/formularz-aiol/`),
- `serwer/formularz-aiol/`: źródła skryptów PHP.
