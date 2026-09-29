# Test „Gdzie Twoja oferta traci klientów?” (v1.0)

Statyczna aplikacja: HTML + CSS + czysty JavaScript. Nie ma frameworków, backendu ani procesu budowania.

| Plik | Co zawiera |
|---|---|
| `content.js` | **Wszystkie teksty** oraz ustawienia formularza MailerLite |
| `styles.css` | Wygląd. Kolory i fonty są w sekcji USTAWIENIA na górze pliku |
| `app.js` | Logika: przechodzenie ekranów, punktacja, formularz, kopiowanie. Nie trzeba go ruszać |
| `index.html` | Strona, która uruchamia test |
| `fonts/` | Font Instrument Serif (licencja SIL OFL). Jest hostowany lokalnie, bez Google Fonts |
| `testy.html` | Sprawdzenie punktacji. Otwórz go w przeglądarce, wszystko ma mieć „OK” |

## Jak edytować teksty

1. Otwórz `content.js` w edytorze tekstu (VS Code, Notepad++, TextEdit w trybie zwykłego tekstu). Nie używaj Worda.
2. Zmieniaj tylko tekst między prostymi cudzysłowami `"…"`. W środku tekstu używaj polskich cudzysłowów `„…”`.
3. Nie usuwaj przecinków na końcu linii ani nawiasów.
4. `*gwiazdki*` w nagłówku oznaczają żółte zaznaczenie markerem.
5. Do uzupełnienia są:
   - pola `badania` w każdym z 7 mechanizmów,
   - sekcja `cta`: tekst o mini-kursie, etykieta przycisku i link do zapisu.
6. Jeśli zmienisz punkty albo przypisanie pytań do mechanizmów, otwórz `testy.html` i sprawdź, czy wszystko ma „OK”.

Treść zgody i klauzula informacyjna RODO są w sekcji `formularz`. To propozycja zgodna z RODO i Prawem komunikacji elektronicznej. Warto, żeby przed startem przejrzał ją prawnik.

Link `linkPolityki` prowadzi do `https://agnieszkakorach.com/polityka-prywatnosci/`. **Ta strona musi istnieć przed startem.**

## Jak podłączyć MailerLite

Dopóki `adresFormularza` w `content.js` jest pusty, działa **tryb testowy**: wyniki pokazują się bez wysyłki e-maila, a na ekranie wyników widać napis „Tryb testowy”.

1. W MailerLite wejdź w **Forms → Embedded forms** i utwórz formularz:
   - wystarczy jedno pole **Email**, bo zgodę i klauzulę pokazuje test;
   - przypisz grupę, np. „Test oferty”;
   - **wyłącz reCAPTCHA**, bo z nią wysyłka z testu nie przejdzie.
2. Zapisz formularz. W zakładce **Embed** skopiuj kod **HTML**.
3. Znajdź w kodzie fragment `action="https://assets.mailerlite.com/jsonp/…/subscribe"`. Wklej ten adres do `adresFormularza`.
4. Sprawdź, czy pole e-mail ma w kodzie `name="fields[email]"`. Sprawdź też ukryte pola (`ml-submit`, `anticsrf`). Jeśli są inne, przepisz je do `poleEmail` i `polaStale`.
5. Opcjonalnie możesz przekazywać wynik i 3 najsłabsze obszary:
   - w **Subscribers → Fields** utwórz pola tekstowe `wynik` i `slabe_obszary` i dodaj je do formularza;
   - w `content.js` wpisz `poleWynik: "fields[wynik]"` i `poleSlabeObszary: "fields[slabe_obszary]"`.
   Pod formularzem pojawi się wtedy automatycznie zdanie o zapisywaniu wyniku.
6. **Przetestuj.** Przejdź test ze swoim adresem i sprawdź w **Subscribers**, czy adres się pojawił. MailerLite nie odsyła aplikacji potwierdzenia, więc ten test trzeba zrobić ręcznie. Jeśli masz włączony double opt-in, przyjdzie mail z prośbą o potwierdzenie zapisu.

Nie wklejaj na stronę całego kodu z MailerLite. Zawiera on skrypt MailerLite i licznik wyświetleń formularza, a test potrzebuje tylko adresu i nazw pól. W kodzie nie ma i nie może być żadnego klucza API.

## Jak wgrać na Zenbox

1. Otwórz panel Zenbox i wejdź w **Menedżer plików** (albo połącz się przez FTP).
2. Otwórz katalog domeny agnieszkakorach.com (zwykle `public_html`).
3. Wgraj cały folder `test-oferty` razem z podfolderem `fonts`.
4. Test działa pod adresem `https://agnieszkakorach.com/test-oferty/`.

Plik `testy.html` możesz wgrać albo pominąć. Ma ustawione `noindex`, więc nie trafi do Google.

## Jak osadzić test na stronie

**A. Link albo przycisk (polecane).** Test jest na tej samej domenie, więc wystarczy:

```html
<a href="/test-oferty/">Sprawdź, gdzie Twoja oferta traci klientów</a>
```

**B. Ramka (iframe).** Wysokość dopasowuje się sama:

```html
<iframe id="test-oferty-ramka" src="https://agnieszkakorach.com/test-oferty/"
        title="Test: Gdzie Twoja oferta traci klientów?"
        style="width:100%;border:0;min-height:600px" allow="clipboard-write"></iframe>
<script>
  window.addEventListener("message", function (e) {
    if (e.origin !== "https://agnieszkakorach.com") return;
    if (e.data && e.data.typ === "test-oferty:wysokosc") {
      document.getElementById("test-oferty-ramka").style.height = e.data.wysokosc + "px";
    }
  });
</script>
```

**C. Wklejony kod.** Działa tylko raz na jednej stronie. Style testu nie wpływają na resztę strony.

```html
<link rel="stylesheet" href="https://agnieszkakorach.com/test-oferty/styles.css">
<div id="test-oferty" class="to-app" lang="pl"></div>
<script src="https://agnieszkakorach.com/test-oferty/content.js"></script>
<script src="https://agnieszkakorach.com/test-oferty/app.js"></script>
```

## Prywatność

- Aplikacja nie ma ciasteczek, localStorage ani skryptów śledzących. Odpowiedzi są tylko w pamięci otwartej karty.
- Font jest wczytywany z tego samego serwera.
- Jedyne połączenie na zewnątrz to wysyłka formularza do MailerLite po kliknięciu „Pokaż moje wyniki”. Poza trybem testowym.

Tytuł karty ustawia `content.js` (`start.tytulStrony`). W `index.html` jest jego kopia na potrzeby podglądów linków w social mediach. Jeśli zmienisz tytuł, zmień go w obu miejscach.
