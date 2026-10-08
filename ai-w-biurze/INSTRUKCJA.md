# AIOfficeLab / 01 FUNDAMENT – landing page z formularzem zgłoszeń

Adres: https://www.officeinfluencers.pl/ai-w-biurze

Wdrożenie ma dwie części:

| Co | Plik | Gdzie trafia |
|---|---|---|
| Strona FUNDAMENT | `aiofficelab-fundament-elementor.html` | widżet **HTML** w Elementorze, strona `ai-w-biurze` |
| Strona SYSTEM | `aiofficelab-system-elementor.html` | widżet **HTML** w Elementorze, strona `ai-w-biurze-system` (zob. „Strona SYSTEM” niżej) |
| Wysyłka zgłoszeń | `formularz-aiol.zip` (folder `formularz-aiol/`) | serwer officeinfluencers.pl (Zenbox) |

Formularz wysyła zgłoszenia na **office@officeinfluencers.pl** przez ten sam sprawdzony skrypt co formularz szkolenia dla asystentek zarządu. Zmieniły się tylko pola i temat wiadomości.

## 1. Wgraj folder `formularz-aiol` na serwer

1. Zaloguj się do panelu Zenbox → **Menedżer plików**. Możesz też użyć FTP, np. FileZilla.
2. Otwórz główny katalog strony officeinfluencers.pl, czyli ten, w którym są `wp-config.php`, `wp-content` i `wp-admin`.
3. Wgraj `formularz-aiol.zip` i **rozpakuj**. Powstanie folder `formularz-aiol` z pięcioma plikami: `formularz.php`, `nip.php`, `bezpieczenstwo.php`, `klucze.php`, `.htaccess`.
   Jeśli folder już jest na serwerze, **zastąp pliki** nowymi, także ukryty `.htaccess` (w Menedżerze plików włącz „Pokaż ukryte pliki”). Wyjątek: jeśli w `klucze.php` jest już wpisany token CEIDG, tego pliku nie nadpisuj.
4. Sprawdzenie: otwórz w przeglądarce `https://www.officeinfluencers.pl/formularz-aiol/formularz.php`. Prawidłowa odpowiedź to komunikat `{"ok":false,"komunikat":"Dozwolona jest tylko metoda POST."}`. Oznacza on, że skrypt działa.
5. Sprawdzenie zabezpieczeń: adresy `https://www.officeinfluencers.pl/formularz-aiol/bezpieczenstwo.php` i `https://www.officeinfluencers.pl/formularz-aiol/` muszą pokazać błąd „Forbidden” (403) albo „Not Found” (404), a nie treść pliku czy listę plików.

> Folderu **nie nazywaj** `ai-w-biurze`. Zasłoniłby stronę WordPressa o tym adresie. Jeśli wybierzesz inną nazwę, popraw ustawienie `formularz` w kodzie strony.

## 1a. Dane firm z CEIDG (Hurtownia Danych)

Po wpisaniu NIP-u formularz sam uzupełnia nazwę i adres firmy. Najpierw pyta wykaz podatników VAT Ministerstwa Finansów (spółki i firmy z VAT). Jeśli tam firmy nie ma albo brakuje adresu, pyta CEIDG, czyli rejestr jednoosobowych działalności, także tych bez VAT. Do CEIDG potrzebny jest token z Hurtowni Danych.

1. W panelu Hurtowni Danych (dane.biznes.gov.pl) skopiuj token do API CEIDG. To długi ciąg znaków zaczynający się zwykle od `eyJ`.
2. Zenbox → **Menedżer plików** → folder `formularz-aiol` → plik `klucze.php` → **Edytuj**.
3. W linii `'ceidg_token' => '',` wklej token między apostrofy, w całości, bez spacji i enterów:
   `'ceidg_token' => 'eyJ...tu cały token...',`
4. Zapisz plik.
5. Sprawdzenie: na stronie wybierz „Firma (faktura VAT)” i wpisz NIP jednoosobowej działalności. Pod polem NIP pojawi się „Dane z CEIDG” albo „Dane z wykazu podatników VAT”, a nazwa i adres uzupełnią się same.

Token jest tajny. Nie wysyłaj go mailem ani na czacie. Z internetu nie da się otworzyć pliku `klucze.php`, a token nie trafia na stronę ani do przeglądarki odwiedzających. Jeśli token wyciekł albo wygasł, wygeneruj nowy w Hurtowni Danych i podmień go w `klucze.php`.

Gdy CEIDG nie odpowiada albo token jest zły, formularz dalej działa: nazwę i adres wpisuje się wtedy ręcznie. W logach błędów PHP w panelu Zenbox pojawi się wpis `CEIDG: odpowiedz HTTP 401` (albo 403), który oznacza zły lub wygasły token.

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

## Strona SYSTEM (AIOfficeLab / 02)

Druga strona, na tym samym szablonie co FUNDAMENT. Proponowany adres: https://www.officeinfluencers.pl/ai-w-biurze-system/

1. **Serwer:** wgraj aktualny `formularz-aiol.zip` tak jak w kroku 1 (zastąp pliki; `klucze.php` z wpisanym tokenem zostaw). Nowy `formularz.php` obsługuje oba szkolenia: rozpoznaje je po ukrytym polu `szkolenie` i sam dobiera nazwę, cenę i temat maila. Strony nie mogą podać własnej ceny.
2. **Strona:** WordPress → Strony → **Dodaj nową**, slug `ai-w-biurze-system`. Dalej jak w kroku 2, tylko wklejasz plik `aiofficelab-system-elementor.html`.
   Jeśli wybierzesz inny adres, popraw go w trzech miejscach kodu: komentarz na górze oraz `"url"` i `"@id"` w bloku `application/ld+json` na dole (wyszukaj `ai-w-biurze-system`).
3. **Płatność online:** w ustawieniach na górze kodu wklej link z EasyTools do SYSTEMU w `platnosc: ""`. Dopóki pole jest puste, strona nie pokazuje przycisków „Kupuję i przechodzę do płatności”, tylko formularz.
4. **Test:** wyślij zgłoszenie. Mail ma temat „Zgłoszenie: AIOfficeLab SYSTEM (AI w biurze)”, w treści: terminy, follow-up i wartość liczba osób × 1190 zł.

**Zmiana terminów** (są w trzech miejscach):

- ustawienia na górze kodu: `termin` (trzy spotkania) i `followUp`: z nich biorą się terminy w hero, karcie z ceną, na końcu strony, w pasku i w mailu,
- sekcja „Terminy” (kafelki z dniami): wyszukaj w kodzie `aiol-session`; każdy kafelek ma dzień, miesiąc z rokiem, dzień tygodnia i `datetime`,
- dane strukturalne na dole kodu: `startDate` (pierwsze spotkanie) i `endDate` (koniec trzeciego).

**SEO (Yoast / Rank Math):**

- Tytuł SEO: `AI w zarządzaniu informacją i projektami – szkolenie online | AIOfficeLab SYSTEM`
- Opis meta: `Szkolenie online LIVE (3 × 2 h + follow-up) dla Executive Assistants, Asystentek Zarządu i Office Managerów: briefingi, dashboardy projektów, informacje z wielu źródeł i follow-up ze spotkań. 1190 zł netto + VAT.`
- Fraza kluczowa: `AI dla asystentki zarządu`
- FAQ ma 32 pytania i jest w danych strukturalnych. Po zmianie pytania na stronie popraw je także w bloku `application/ld+json`.

## Ustawienia (na samej górze kodu strony)

```js
window.AIOL_CONFIG = {
  formularz: "/formularz-aiol/formularz.php", // skrypt wysyłający zgłoszenie
  platnosc: "https://easl.ink/XD2bl",         // link do płatności online
  termin: "czwartek, 5 listopada 2026, godz. 10.00–12.00", // data i godziny edycji
  photoUrl: "",                               // inne zdjęcie prowadzącej (puste = wbudowane)
  stickyBar: true                             // pływający pasek z przyciskiem
};
```

- **formularz**: adres skryptu na tej samej stronie, zaczyna się od `/`. Adresu z inną domeną strona nie przyjmie, żeby dane ze zgłoszeń nie mogły trafić gdzie indziej.
- **termin**: data i godziny edycji, teraz „czwartek, 5 listopada 2026, godz. 10.00–12.00”. Termin pojawia się w pasku na górze, w hero, w karcie z ceną, w finale, w pływającym pasku i w mailu ze zgłoszeniem. Puste pole = bez terminu.
  Po zmianie terminu popraw też `"startDate": "2026-11-05T10:00:00+01:00"` i `"endDate": "2026-11-05T12:00:00+01:00"` w bloku `application/ld+json` na dole kodu (rok-miesiąc-dzień, godzina; `+01:00` zimą, `+02:00` latem).
- **Zdjęcie prowadzącej** (z konferencji) jest wbudowane w kod strony w sekcji „Prowadząca”. Nie trzeba go nigdzie wgrywać. Hero jest bez zdjęcia.
- **photoUrl**: jeśli chcesz inne zdjęcie, wejdź w Media → Biblioteka → wybierz zdjęcie → „Kopiuj adres URL do schowka” i wklej między cudzysłowy. Adres musi zaczynać się od `https://` albo `/`. Najlepiej zdjęcie pionowe (4:5) z twarzą w górnej części kadru.
- **platnosc**: link do płatności online, otwierany przez przyciski „Kupuję i przechodzę do płatności”. Musi zaczynać się od `https://`. Pusty cudzysłów `""` usuwa te przyciski ze strony, np. gdy sprzedaż online jest zamknięta.
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
- **Dane:** na serwerze nie zostaje nic ze zgłoszeń. Skrypt zapisuje tylko liczniki prób (skrót adresu IP i godzina) w katalogu tymczasowym i regularnie usuwa te starsze niż dwie godziny.

## Zabezpieczenia

**Skrypty na serwerze (`formularz-aiol`)**

- Z zewnątrz da się otworzyć tylko `formularz.php` i `nip.php`. Plik pomocniczy, kopie zapasowe, pliki ukryte i lista plików w folderze są zablokowane.
- Zgłoszenie przyjmowane jest tylko ze strony officeinfluencers.pl. Skrypt sprawdza nagłówki Origin, Referer i Sec-Fetch, więc cudza strona nie wyśle zgłoszenia w imieniu odwiedzającego.
- Odbiorca, nadawca i temat maila są na sztywno w kodzie. Adres e-mail ze zgłoszenia przechodzi ścisłą kontrolę, zanim trafi do „Odpowiedz”, więc nie da się nim dopisać ukrytych odbiorców.
- Skrypt przyjmuje tylko pola, które wysyła strona, każde z limitem długości. Dodatkowe pola, tablice, pliki i za duże żądania są odrzucane. Termin z formularza może zawierać tylko datę i godzinę.
- Limity: 30 prób i 5 wysłanych zgłoszeń na godzinę z jednego adresu IP, a sprawdzanie NIP-u do 6 razy na 2 minuty i 25 razy na godzinę.
- Sprawdzanie NIP-u pyta wyłącznie wykaz podatników VAT Ministerstwa Finansów i CEIDG. NIP musi mieć 10 cyfr z poprawną cyfrą kontrolną, więc skryptu nie da się użyć do odpytywania innych serwerów.
- Token CEIDG leży w `klucze.php` na serwerze. Pliku nie da się otworzyć z internetu, a token jest sprawdzany przed użyciem, żeby błędnie wklejony tekst nie trafił do zapytania.
- Odpowiedzi skryptów mają nagłówki blokujące osadzanie, zgadywanie typu treści, indeksowanie i zapisywanie w pamięci podręcznej. Błędy PHP nie są pokazywane odwiedzającym.
- Treść maila jest oczyszczona z niewidocznych znaków i ukrytych poleceń dla programów AI (prompt injection), a podejrzane fragmenty są oznaczone na górze wiadomości.

**Kod strony**

- Strona nie wstawia żadnego tekstu jako HTML. Termin, komunikaty serwera i dane z rejestru trafiają na stronę jako zwykły tekst.
- Adresy z ustawień są sprawdzane. Formularz wysyła tylko na adres na tej samej stronie, zdjęcia ładują się tylko z `https://` albo z tej samej strony, a link płatności musi zaczynać się od `https://`. Błędny adres jest pomijany.
- Jedyne zewnętrzne zasoby to fonty Google (bez przekazywania adresu strony) i link do płatności. Zdjęcia są wbudowane w kod.

**Poza tym kodem** największe ryzyko dotyczy samego WordPressa:

- aktualne WordPress, motyw i wtyczki,
- logowanie dwuetapowe dla kont administratorów,
- regularne kopie zapasowe w panelu Zenbox.

## Wygląd

Strona ma białe tło, czarne sekcje i żółte akcenty, bez beżu. Całość używa jednego prostego kroju: **Inter**. Hero jest białe, bez zdjęcia: nagłówek po lewej, opis i przyciski po prawej. Czarne są: opinie, pytania z sali i sekcja końcowa. W białych sekcjach kafelki są białe z ramką, szare, czarne albo żółte. Żółte są przyciski „Zapisuję się”, pasek z terminem na górze, plakietki nad nagłówkami i kilka kafelków. Wyróżnione słowa w nagłówkach mają na białym tle żółte zakreślenie, a na czarnym są żółte z cienkim podkreśleniem.

Kolejność sekcji: pasek z terminem → hero → klienci (przewijana taśma) → dla kogo i o szkoleniu → puenta „Nie jak napisać prompt” → opinie → program (4 moduły) → po szkoleniu i dalsza ścieżka → pytania z sali → prowadząca → cena z formularzem zgłoszenia → dla firm i kontakt → FAQ w dwóch kolumnach → finał.

### Animacje

- kafelki i nagłówki płynnie wjeżdżają, gdy pojawiają się na ekranie,
- żółte zakreślenie albo podkreślenie rysuje się pod wyróżnionymi słowami w nagłówkach,
- nazwy klientów przewijają się w taśmie (zatrzymuje się po najechaniu kursorem),
- cienki żółty pasek na samej górze pokazuje, ile strony już przeczytano,
- przyciski i ramki kafelków delikatnie zmieniają kolor po najechaniu kursorem, a kwota w karcie z ceną „podskakuje” po zmianie liczby osób.

Jeśli ktoś ma w systemie włączone ograniczanie ruchu, strona wyświetla się bez animacji. Bez JavaScriptu cała treść też jest widoczna od razu.

### Kolory

| Zmienna | Do czego | Wartość |
|---|---|---|
| `--aiol-cta` | przyciski „Zapisuję się” i wszystkie żółte akcenty | `#f7c531` |
| `--aiol-cta-hover` | przycisk po najechaniu kursorem | `#ffd24d` |
| `--aiol-soft` | szare kafelki (na białym) | `#f2f2f2` |
| `--aiol-line` | ramki i cienkie linie (na białym) | `#e3e3e3` |
| `--aiol-ink` | nagłówki (na białym) | `#111111` |
| `--aiol-text` | zwykły tekst (na białym) | `#3d3d3d` |

Czarne sekcje i kafelki mają klasę `aiol-dark`. Ich kolory (tło `#0c0c0c`, kafelki `#161616`, tekst biały) są w regule `#aiol .aiol-dark` na początku stylów. Żeby zmienić sekcję z białej na czarną, dopisz `aiol-dark` do jej klasy, np. `class="aiol-sec aiol-dark"`.

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
- **Adres `formularz.php` pokazuje „500 Internal Server Error” zaraz po wgraniu plików:** serwer nie przyjmuje reguł dostępu z pliku `.htaccess`. Poproś pomoc Zenbox o włączenie `AllowOverride AuthConfig` dla tego folderu. Do tego czasu możesz usunąć `.htaccess`: plik `bezpieczenstwo.php` i tak sam blokuje bezpośrednie otwarcie.
- **Pojawia się „Żądanie spoza strony officeinfluencers.pl”:** strona i folder `formularz-aiol` muszą być w tej samej domenie (`www.officeinfluencers.pl`). Ustawienie `formularz` zaczyna się od `/`, bez `https://`.
- **Formularz pokazuje „Zgłoszenie przyjęte”, ale mail nie dochodzi:** sprawdź spam. Następnie w panelu Zenbox sprawdź, czy istnieje skrzynka office@officeinfluencers.pl i czy rekord SPF domeny obejmuje serwer Zenbox.
- **NIP nie uzupełnia danych:** rejestr Ministerstwa Finansów bywa chwilowo niedostępny. Wtedy nazwę i adres wpisuje się ręcznie, a zgłoszenie i tak dochodzi.
- **Strona jest wąska albo ma ramki po bokach:** kontener nie ma pełnej szerokości albo ma padding (krok 2.3).
- **Przyciski nie przewijają do formularza albo kafelki pojawiają się dopiero po ruchu myszką** (WP Rocket „Opóźnij JavaScript”, LiteSpeed Cache, Autoptimize): dodaj do wyjątków `AIOL_CONFIG` oraz `aiol`.
- **Po kliknięciu sekcja chowa się pod przyklejonym nagłówkiem:** zwiększ wartość `scroll-margin-top: 100px` (występuje w kodzie 3 razy).
- **Fonty:** kod ładuje Inter z Google Fonts. Jeśli serwis ładuje go lokalnie (Elementor → Ustawienia → Wydajność), możesz usunąć 3 linie `<link …fonts.googleapis.com…>` z początku kodu.

## Pliki w repozytorium

- `aiofficelab-fundament-elementor.html`: kod strony FUNDAMENT do Elementora,
- `aiofficelab-system-elementor.html`: kod strony SYSTEM do Elementora,
- `formularz-aiol.zip`: paczka do wgrania na serwer (ta sama zawartość co `serwer/formularz-aiol/`),
- `serwer/formularz-aiol/`: źródła skryptów PHP.
