# SYSTEM — współpracy z CEO napędzany AI i psychologią biznesu

Landing page programu, docelowy adres:
`https://officeinfluencers.pl/executive-assistant-system-ceo`

## Pliki

```
index.html                     strona
css/style.css                  style (tokeny kolorów, motyw jasny i ciemny)
css/fonts.css                  deklaracje @font-face
fonts/*.woff2                  fonty self-hosted (Archivo, Source Serif 4, IBM Plex Mono)
js/script.js                   zakreślacz, wejścia sekcji, kotwice
build-preview.py               skleja całość w jeden plik do podglądu
podglad/system-ceo-preview.html   wynik powyższego skryptu
```

Strona nie pobiera niczego z zewnętrznych serwerów — fonty są hostowane
razem z nią (subsety `latin` + `latin-ext`, konieczne dla polskich znaków).

## Wdrożenie

Wgraj zawartość tego katalogu (bez `build-preview.py`, `podglad/` i tego pliku)
do katalogu odpowiadającego adresowi `/executive-assistant-system-ceo`.
Ścieżki są względne, więc nic nie trzeba zmieniać.

Jeżeli `officeinfluencers.pl` stoi na WordPressie, ta strona nie wejdzie
w istniejący motyw jako zwykły wpis — trzeba ją albo wgrać jako statyczny
katalog obok WordPressa, albo przepisać na szablon motywu.

## Do uzupełnienia przed publikacją

Wszystkie braki są w kodzie oznaczone klasą `todo` i widoczne na stronie jako
żółte etykiety z przerywaną ramką. Przyciski bez docelowego adresu mają klasę
`btn--todo` i dopisek „do podpięcia".

Aby wypisać wszystkie naraz:

```
grep -o 'class="todo"[^<]*>[^<]*' index.html
```

Braki według sekcji:

| Sekcja | Do uzupełnienia |
|---|---|
| Webinar | data webinaru, link do zapisu |
| Ta część jest dla CEO | plik do pobrania dla CEO, formularz dla firm |
| Cena | link do zakupu, formularz dla firm |
| Prowadzące | dane liczbowe Agnieszki Korach (3 pozycje), opis Anny Osińskiej (4 pozycje) |
| Opinie | 4 cytaty z imieniem, stanowiskiem i firmą |
| Zobacz jedną lekcję | link do formularza zapisu |
| Nadal nie wiesz | link do kalendarza |
| Kontakt | e-mail, telefon, link do kalendarza |
| Newsletter | nazwa, opis, link zapisu |
| Stopka | nazwa organizatora, adres, NIP, e-mail, telefon, regulamin, polityka prywatności, zasady płatności |

Formularze e-mail (lekcja próbna, newsletter) mają dziś `onsubmit="return false;"`
— po podpięciu narzędzia mailingowego trzeba podmienić je na właściwy `action`.

## Podgląd jednoplikowy

```
python3 build-preview.py
```

Wersja z `podglad/` służy tylko do pokazania strony bez serwera. Źródłem prawdy
pozostają `index.html`, `css/` i `js/`.
