# OFFICE MANAGER AI OPERATIONS — landing `/ai-office-manager`

Samodzielny plik `index.html`: cały CSS i JS jest w środku, bez zależności
poza Google Fonts (Plus Jakarta Sans). Style są zamknięte w klasie `.omai`,
więc nie kolidują z motywem WordPress.

## Publikacja w WordPressie

1. Utwórz stronę o adresie (slug) `ai-office-manager`.
2. Wklej zawartość **od `<main class="omai">` do `</main>`** oraz oba bloki
   `<script>` i `<style>` w bloku **Custom HTML** (albo w szablonie strony).
   Alternatywnie użyj całego pliku jako szablonu strony w motywie potomnym.
3. W ustawieniach SEO (Yoast / Rank Math) ustaw:
   - **SEO title:** `AI dla Office Managera – 4-tygodniowy program | Office Influencers`
   - **Meta description:** `Praktyczny 4-tygodniowy program AI dla Office Managerów. Własny Asystent AI, workflow, weryfikacja informacji, podstawy automatyzacji i sesja z prawnikiem.`
   - Wyłącz automatyczne dodawanie `<h1>` z tytułu strony, żeby na stronie
     pozostał **jeden H1**: `OFFICE MANAGER AI OPERATIONS`.

## Do uzupełnienia przed publikacją

| Element | Gdzie | Co zrobić |
|---|---|---|
| Zdjęcie Agnieszki Korach | sekcja „Prowadzi Agnieszka Korach" | Podmień blok `.omai-todo` na `<img>` z Biblioteki mediów. Gotowy kod jest w komentarzu HTML nad sekcją. |
| Opinie | sekcja „Co mówią uczestniczki…" | Wklej 2–3 **prawdziwe** opinie z istniejących podstron Office Influencers. Szablon karty w komentarzu HTML nad sekcją. Zachowaj dokładnie imię, nazwisko, stanowisko i firmę. |
| Terminy edycji | sekcja cenowa, blok „Terminy" | Wstaw daty 4 spotkań i sesji z prawnikiem w miejsce „termin do potwierdzenia". |
| Logotypy klientów | pasek wiarygodności | Opcjonalnie zamień nazwy tekstowe (`<span class="omai-logo">`) na pliki logotypów z Biblioteki mediów. |
| Formularz | sekcja `#zapisy` | Zgłoszenia idą na **office@officeinfluencers.pl**. Bez backendu działa przez `mailto` (patrz niżej) — zalecane podłączenie wtyczki formularza. |
| Polityka prywatności | checkbox zgody | Sprawdź, czy link `/polityka-prywatnosci/` jest poprawny, i uzupełnij klauzulę informacyjną RODO zgodnie z obowiązującą na stronie. |

## Formularz zgłoszeniowy

Odbiorca zgłoszeń: **office@officeinfluencers.pl**, ustawiony w jednym
miejscu — atrybut `data-mailto` na `<form id="omai-form">`.

Pola: imię i nazwisko, stanowisko, firma, e-mail, telefon (opcjonalny),
liczba zgłaszanych osób (1–10), wiadomość (opcjonalna), checkbox zgody.

Formularz ma nad polami widoczny pasek informujący, że zgłoszenie dotyczy
programu OFFICE MANAGER AI OPERATIONS — ta sama informacja trafia do tematu
i treści maila.

**Jak działa teraz (bez backendu):** po walidacji strona składa gotową
wiadomość i otwiera ją w programie pocztowym użytkownika (`mailto:`).
Temat: `Zgłoszenie: OFFICE MANAGER AI OPERATIONS – <Firma>`. W treści są
wszystkie pola plus adnotacja o wyrażonej zgodzie. Na ekranie potwierdzenia
jest zapasowy link `mailto` na wypadek, gdyby okno się nie otworzyło.

**Ograniczenie:** `mailto` wymaga skonfigurowanego klienta poczty. Część osób
(zwłaszcza korzystających z poczty w przeglądarce) nie dokończy wysyłki,
a zgłoszenia nie zapisują się nigdzie po stronie serwera.

**Zalecane przy publikacji:** podmień `<form id="omai-form">` na formularz
wtyczki (Contact Form 7 / WPForms / Gravity Forms) i ustaw w niej:
- odbiorcę: `office@officeinfluencers.pl`,
- temat: `Zgłoszenie: OFFICE MANAGER AI OPERATIONS`,
- te same pola i tę samą treść zgody.

Klasy CSS (`omai-field`, `omai-consent`, `omai-btn omai-btn--primary`) możesz
przenieść na pola wtyczki, żeby zachować wygląd.

## Źródła danych użytych na stronie

Wszystkie liczby i nazwy klientów pochodzą z istniejących stron Office
Influencers (`officeinfluencers.pl/o-mnie/`): 25+ lat doświadczenia,
10 000+ przeszkolonych osób, klienci: Mercedes-Benz, Bosch, 3M, Allen & Overy,
Geberit, ERGO Hestia, Maspex, ABB, Solid Security; psycholog społeczny (SWPS),
certyfikowana AI Educator (CampusAI). **Przed publikacją potwierdź je jeszcze
raz na aktualnej wersji strony.** Na landingu nie ma żadnych wymyślonych
danych, opinii, klientów, rabatów ani liczby pozostałych miejsc.

## Sprawdzone

- brak poziomego przewijania przy 390 px i 1440 px,
- jeden `<h1>`, wszystkie kotwice CTA prowadzą do `#zapisy` (albo `#cena`
  dla „Pokażę to przełożonemu"),
- walidacja formularza blokuje puste pola, brak zgody oraz liczbę osób spoza
  zakresu 1–10; po wysyłce pokazuje potwierdzenie kolejnego kroku,
- accordion FAQ działa bez JS (`<details>`),
- polskie znaki diakrytyczne (UTF-8).
