# Executive Assistant: System współpracy z CEO

Landing page programu, docelowy adres:
`https://officeinfluencers.pl/executive-assistant-system-ceo`

## Pliki

```
index.html                        strona
regulamin.html                    regulamin (szkielet z danymi firmy)
polityka-prywatnosci.html         polityka prywatności (szkielet z danymi firmy)
css/style.css                     style
css/fonts.css                     deklaracje @font-face
fonts/inter-var-*.woff2           Inter, self-hosted (font zmienny, 2 pliki)
js/script.js                      zdjęcie, kotwice, zapas dla starszych przeglądarek
zdjecie/agnieszka-korach.webp     zdjęcie prowadzącej w hero
build-preview.py                  skleja całość w jeden plik do podglądu
podglad/system-ceo-preview.html   wynik powyższego skryptu
```

Strona nie pobiera niczego z zewnętrznych serwerów.

## Zdjęcie w hero

Sylwetka jest wycięta z tła (WebP z przezroczystością, 900 x 1071 px, 83 KB)
i stoi na żółtej płycie z czarną krawędzią. Kadr obejmuje głowę i tors,
do splecionych rąk.

Hero ma dwie kolumny: **cały tekst po lewej** (pasek dat, nadtytuł, nagłówek,
akapity, przyciski), **zdjęcie po prawej**. Na wąskim ekranie kolumny układają
się jedna pod drugą, najpierw tekst, potem zdjęcie.

Zdjęcie zajmuje **34% szerokości hero**, przy zadanym limicie 40%. Steruje
tym proporcja kolumn w regule `.hero__grid` (`1.5fr` na tekst, `1fr` na
zdjęcie). Zwiększenie drugiej wartości przekroczy limit.

Podmiana: wgraj plik pod tą samą nazwą. Przy innych proporcjach popraw
`aspect-ratio` w `.photo__frame`, dziś `5 / 6`. Gdyby pliku zabrakło,
pokaże się ramka z instrukcją.

## Design

**Kolory 60/30/10.** Zmierzone na pełnym zrzucie strony: 58% bieli,
31% czerni, 10% żółtego. Proporcje trzyma rytm pasów:

| Sekcja | Tło |
|---|---|
| Hero, Problem, Dla kogo, Jak pracujemy, Zadania, Prowadzące, Cena, FAQ, Kontakt | biel |
| Pasek z pytaniami, Program, Maszynownia AI, Dla CEO, Opinie, Zamknięcie, Stopka | czerń |
| Czego się nauczysz, Lekcja próbna, Rozmowa, panel cenowy, płyta pod zdjęciem | żółć |

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

Strona nie podaje limitu miejsc i nie nazywa tej edycji pierwszą kohortą.

Trzy wątki przewijają się przez moduły i przez listę „Czego się nauczysz":
wypracowywanie gotowych rozwiązań (moduł 02), komunikacja dopasowana do
osobowości i stylu pracy CEO (moduł 03, także 02 przy formacie dokumentu)
oraz praca w tempie na wysokich obrotach (moduły 01 i 04).

**Warunki zespołowe:** od 3 do 5 osób 5% rabatu, od 6 do 8 osób 10%,
od 9 osób 15%. Wartości występują w dwóch miejscach: karta „Zespół"
w sekcji Cena oraz odpowiedź w FAQ. Przy zmianie popraw oba.

## Dane organizatora

Wpisane w stronie, w stopce, w danych strukturalnych oraz w obu dokumentach:

```
ARK Consulting Agnieszka Korach
ul. Ołówkowa 1d/68, 05-800 Pruszków
NIP 5291466035
szkolenia@arkconsulting.com.pl
504 243 881
```

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
| Prowadzące | dane liczbowe Agnieszki Korach (3), opis Anny Osińskiej (4) |
| Opinie | 4 cytaty z imieniem, stanowiskiem i firmą |
| Dla CEO | plik dla CEO, formularz dla firm |
| Cena | link zakupu, formularz dla firm |
| Lekcja próbna | link formularza |
| Rozmowa | link do kalendarza |
| Kontakt | nazwa i opis newslettera, link zapisu |

Aby wypisać wszystkie naraz:

```
grep -o 'class="todo">[^<]*' index.html
```

Formularze e-mail mają dziś `onsubmit="return false;"`. Po podpięciu narzędzia
mailingowego trzeba podmienić je na właściwy `action`.

### Regulamin i polityka prywatności

Obie strony zawierają komplet danych firmy oraz warunki, które wynikają
z treści oferty (terminy, ceny, rabaty, formy płatności, dostęp do materiałów,
zasady certyfikatu). Klauzule prawne zostały wypisane jako lista braków,
a nie napisane, żeby nie tworzyć pozorów stanu prawnego, którego nikt nie
zweryfikował. Do uzupełnienia z prawnikiem: odstąpienie od umowy, reklamacje,
prawa autorskie, odpowiedzialność, podstawy przetwarzania danych, okresy
przechowywania, odbiorcy danych i cookie.

## Podgląd jednoplikowy

```
python3 build-preview.py
```

Wersja z `podglad/` służy tylko do pokazania strony bez serwera. Źródłem prawdy
pozostają `index.html`, `css/` i `js/`.
