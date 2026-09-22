# OFFICE MANAGER AI OPERATIONS — landing `/ai-office-manager`

Statyczna strona dla hostingu **Zenbox** (bez WordPressa). Formularz działa
na PHP, reszta to zwykły HTML.

```
ai-office-manager/
├── index.html    ← cała strona (HTML + CSS + JS w jednym pliku)
├── wyslij.php    ← obsługa formularza, wysyłka maila
└── .htaccess     ← kodowanie, kompresja, cache, nagłówki
```

Poza Google Fonts (Plus Jakarta Sans) strona nie ma żadnych zależności
zewnętrznych — żadnych bibliotek, frameworków ani buildu.

## Publikacja na Zenboxie

1. Wejdź do panelu Zenbox → **Menedżer plików** (albo połącz się przez FTP/SFTP).
2. Wgraj wszystkie trzy pliki do katalogu `public_html/ai-office-manager/`.
   Strona będzie dostępna pod `https://twoja-domena.pl/ai-office-manager/`.
   Jeśli ma być w katalogu głównym domeny — wgraj je do `public_html/`.
3. Sprawdź w panelu, że katalog obsługuje **PHP 8.x** (domyślnie tak).
4. Otwórz `wyslij.php` i ustaw u góry pliku:
   - `ODBIORCA` — dokąd idą zgłoszenia (teraz: `office@officeinfluencers.pl`),
   - `NADAWCA` — **musi być adresem w domenie, z której działa strona**
     (teraz: `formularz@officeinfluencers.pl`). Załóż tę skrzynkę albo alias
     w panelu Zenbox. Jeśli zostawisz tu adres zgłaszającego, poczta odbiorcy
     odrzuci wiadomość lub wrzuci ją do spamu (SPF/DKIM).
5. Wyślij testowe zgłoszenie i sprawdź, czy mail dotarł.

### Adres strony w metadanych

W `index.html` adres `https://www.officeinfluencers.pl/ai-office-manager/`
występuje **4 razy**: `<link rel="canonical">`, `og:url`, oraz dwa razy
w danych strukturalnych (`Course.url` i `Offer.url`). Jeśli strona stanie
pod innym adresem, podmień wszystkie cztery.

### Uwaga, jeśli domena obsługuje też WordPressa

Landing może stać w podkatalogu obok WordPressa — reguły WP w `.htaccess`
przepuszczają istniejące katalogi i pliki (`RewriteCond !-f` / `!-d`), więc
`/ai-office-manager/` zadziała bez zmian w konfiguracji WP.

## Formularz zgłoszeniowy

Odbiorca: **office@officeinfluencers.pl** (stała `ODBIORCA` w `wyslij.php`).

Pola: imię i nazwisko, stanowisko, firma, e-mail, telefon (opcjonalny),
liczba zgłaszanych osób (1–10), wiadomość (opcjonalna), checkbox zgody.

Nad polami jest widoczny pasek z informacją, że zgłoszenie dotyczy programu
OFFICE MANAGER AI OPERATIONS — ta sama informacja trafia do tematu maila.

**Jak to działa:**
- z JavaScriptem: POST w tle, potwierdzenie pojawia się bez przeładowania,
- bez JavaScriptu: zwykły POST, a `wyslij.php` przekierowuje na
  `index.html?wyslano=1#zapisy` i strona sama pokazuje potwierdzenie,
- błąd wysyłki: komunikat nad przyciskiem z adresem do kontaktu bezpośredniego.

**Mail, który przychodzi:**

```
Do:       office@officeinfluencers.pl
Od:       Formularz Office Influencers <formularz@officeinfluencers.pl>
Reply-To: <adres zgłaszającego>          ← odpowiadasz jednym kliknięciem
Temat:    Zgłoszenie: OFFICE MANAGER AI OPERATIONS – <Firma>

Wszystkie pola formularza + adnotacja o zgodzie, data i IP zgłoszenia.
```

**Zabezpieczenia:** ukryte pole-pułapka na boty, walidacja po stronie
serwera (nie tylko w przeglądarce), usuwanie znaków nowej linii z nagłówków
(ochrona przed wstrzyknięciem nagłówków), limit 1–10 osób.

**Czego formularz nie robi:** nie przyjmuje płatności i nie rezerwuje miejsca —
jest to napisane wprost pod przyciskiem. Zgłoszenia nie zapisują się do bazy;
jedynym śladem jest mail. Jeśli chcesz mieć je też w panelu, trzeba dołożyć
zapis do pliku CSV albo bazy.

## Do uzupełnienia przed publikacją

| Element | Gdzie | Co zrobić |
|---|---|---|
| Adres nadawcy | `wyslij.php`, stała `NADAWCA` | Załóż skrzynkę/alias w domenie strony i wpisz tutaj. Bez tego maile mogą nie docierać. |
| Zdjęcie Agnieszki Korach | sekcja „Prowadzi Agnieszka Korach" | Wgraj plik obok `index.html` i podmień blok `.omai-todo` na `<img>`. Gotowy kod jest w komentarzu HTML nad sekcją. |
| Opinie | sekcja „Co mówią uczestniczki…" | Wklej 2–3 **prawdziwe** opinie z istniejących stron Office Influencers. Szablon karty w komentarzu HTML nad sekcją. Zachowaj dokładnie imię, nazwisko, stanowisko i firmę. |
| Terminy edycji | sekcja cenowa, blok „Terminy" | Wstaw daty 4 spotkań i sesji z prawnikiem w miejsce „termin do potwierdzenia". |
| Logotypy klientów | pasek wiarygodności | Opcjonalnie zamień nazwy tekstowe (`<span class="omai-logo">`) na pliki logotypów. |
| Polityka prywatności | checkbox zgody | Link prowadzi do `/polityka-prywatnosci/`. Popraw adres i uzupełnij klauzulę informacyjną RODO. |
| Adres strony | `index.html`, 4 miejsca | Podmień, jeśli landing stanie pod innym adresem niż `officeinfluencers.pl/ai-office-manager/`. |

## Źródła danych użytych na stronie

Wszystkie liczby i nazwy klientów pochodzą z istniejących stron Office
Influencers (`officeinfluencers.pl/o-mnie/`): 25+ lat doświadczenia,
10 000+ przeszkolonych osób, klienci: Mercedes-Benz, Bosch, 3M, Allen & Overy,
Geberit, ERGO Hestia, Maspex, ABB, Solid Security; psycholog społeczny (SWPS),
certyfikowana AI Educator (CampusAI). **Przed publikacją potwierdź je jeszcze
raz na aktualnej wersji strony.** Na landingu nie ma żadnych wymyślonych
danych, opinii, klientów, rabatów ani liczby pozostałych miejsc.

## SEO

Ustawione bezpośrednio w `index.html`, nic nie trzeba dopisywać:

- **title:** `AI dla Office Managera – 4-tygodniowy program | Office Influencers`
- **meta description:** `Praktyczny 4-tygodniowy program AI dla Office Managerów. Własny Asystent AI, workflow, weryfikacja informacji, podstawy automatyzacji i sesja z prawnikiem.`
- jeden `<h1>`: `OFFICE MANAGER AI OPERATIONS`
- Open Graph + dane strukturalne `Course` ze schema.org

## Sprawdzone w przeglądarce

- brak poziomego przewijania przy 390 px i 1440 px,
- wszystkie CTA prowadzą do formularza `#zapisy` (poza „Pokażę to przełożonemu"
  → sekcja ceny),
- walidacja blokuje puste pola, brak zgody i liczbę osób spoza zakresu 1–10 —
  zarówno w przeglądarce, jak i po stronie PHP,
- wysyłka maila działa (przetestowana na lokalnym serwerze PHP z przechwytem
  poczty): poprawny odbiorca, temat, `Reply-To` i polskie znaki,
- ścieżka bez JavaScriptu działa i kończy się potwierdzeniem na stronie,
- pułapka na boty i próba wstrzyknięcia nagłówka w pole e-mail — zablokowane,
- accordion FAQ działa bez JS (`<details>`),
- sticky CTA na mobile pojawia się po hero i chowa przy formularzu.
