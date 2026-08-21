# Executive Assistant: System współpracy z CEO

Landing page programu, docelowy adres:
`https://officeinfluencers.pl/executive-assistant-system-ceo`

## Pliki

```
index.html                        strona
css/style.css                     style
css/fonts.css                     deklaracje @font-face
fonts/inter-var-*.woff2           Inter, self-hosted (font zmienny, 2 pliki)
js/script.js                      zdjęcie, kotwice, zapas dla starszych przeglądarek
zdjecie/                          tu wgraj zdjęcie prowadzącej
build-preview.py                  skleja całość w jeden plik do podglądu
podglad/system-ceo-preview.html   wynik powyższego skryptu
```

Strona nie pobiera niczego z zewnętrznych serwerów.

## Zdjęcie w hero

Wgraj plik jako `zdjecie/agnieszka-korach.jpg`. Zalecenia:

- proporcje 4:5 (pionowe), np. 1200 x 1500 px,
- twarz w górnej części kadru, bo strona kadruje od góry,
- do 300 KB, JPG lub WebP (przy WebP zmień rozszerzenie w `index.html`).

Dopóki pliku nie ma, w tym miejscu pokazuje się ramka z instrukcją.

## Design

**Kolory 60/30/10.** Zmierzone na pełnym zrzucie strony: 61% bieli,
29% czerni, 9% żółtego. Proporcje trzyma rytm pasów:

| Sekcja | Tło |
|---|---|
| Hero, Problem, Dla kogo, Webinar, Jak pracujemy, Zadania, Prowadzące, Cena, FAQ, Kontakt | biel |
| Pasek z pytaniami, Program, Maszynownia AI, Dla CEO, Opinie, Zamknięcie, Stopka | czerń |
| Czego się nauczysz, Lekcja próbna, Rozmowa, panel cenowy | żółć |

Zmiana tła którejkolwiek sekcji rozjeżdża proporcje. Można to sprawdzić,
robiąc pełny zrzut strony i licząc piksele.

**Typografia.** Jeden krój, Inter, prowadzony kontrastem skali i grubości:
nagłówki 800 z ujemnym światłem, etykiety 600 wersalikami z dużym światłem,
tekst 400 i 500. Cyfry w tabelach i cenach są tabularne.

**Ruch.** Wejścia sekcji i rysowanie zakreślacza obsługuje natywna oś scrolla
(`animation-timeline: view()`), bez JavaScriptu. Starsze przeglądarki dostają
klasę `js-fallback` i obserwator w `script.js`. Przy włączonym ograniczeniu
ruchu w systemie wszystko jest od razu widoczne i nieruchome.

Strona ma jeden, zadany układ barw i celowo nie odwraca się pod ciemny motyw
systemowy.

## Treść

Strona stawia pytania i nie podaje odpowiedzi, bo odpowiedzi są przedmiotem
programu. Cztery moduły to cztery pytania, a bloki wyróżnione żółtą krechą
nazywają problem, nie rozwiązują go. Utrzymaj tę zasadę przy dopisywaniu
treści, inaczej strona zacznie sprzedawać to, co ma sprzedać kurs.

## Wdrożenie

Wgraj zawartość katalogu (bez `build-preview.py`, `podglad/` i tego pliku)
do katalogu odpowiadającego adresowi `/executive-assistant-system-ceo`.
Ścieżki są względne, więc nic nie trzeba zmieniać.

Jeżeli `officeinfluencers.pl` stoi na WordPressie, ta strona nie wejdzie
w istniejący motyw jako zwykły wpis. Trzeba ją albo wgrać jako statyczny
katalog obok WordPressa, albo przepisać na szablon motywu.

## Do uzupełnienia przed publikacją

Braki są oznaczone w kodzie klasą `todo` i widoczne na stronie jako żółte
etykiety z przerywaną ramką. Przyciski bez adresu mają klasę `btn--todo`
i dopisek „do podpięcia".

| Sekcja | Do uzupełnienia |
|---|---|
| Webinar | data, link zapisu |
| Dla CEO | plik dla CEO, formularz dla firm |
| Prowadzące | dane liczbowe Agnieszki Korach (3), opis Anny Osińskiej (4) |
| Opinie | 4 cytaty z imieniem, stanowiskiem i firmą |
| Cena | link zakupu, formularz dla firm |
| Lekcja próbna | link formularza |
| Rozmowa | link do kalendarza |
| Kontakt | e-mail, telefon, nazwa i opis newslettera, link zapisu |
| Stopka | nazwa organizatora, adres, NIP, e-mail, telefon, regulamin, polityka prywatności, zasady płatności |

Aby wypisać wszystkie naraz:

```
grep -o 'class="todo">[^<]*' index.html
```

Formularze e-mail mają dziś `onsubmit="return false;"`. Po podpięciu narzędzia
mailingowego trzeba podmienić je na właściwy `action`.

## Podgląd jednoplikowy

```
python3 build-preview.py
```

Wersja z `podglad/` służy tylko do pokazania strony bez serwera. Źródłem prawdy
pozostają `index.html`, `css/` i `js/`.
