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
| Formularz | sekcja `#zapisy` | Podłącz backend (Contact Form 7 / WPForms / Gravity Forms) albo własny endpoint w `action`. Bez backendu formularz pokazuje wyłącznie potwierdzenie kolejnego kroku. |
| Polityka prywatności | checkbox zgody | Sprawdź, czy link `/polityka-prywatnosci/` jest poprawny, i uzupełnij klauzulę informacyjną RODO zgodnie z obowiązującą na stronie. |

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
- walidacja formularza blokuje puste pola i brak zgody, po wysyłce pokazuje
  potwierdzenie kolejnego kroku,
- accordion FAQ działa bez JS (`<details>`),
- polskie znaki diakrytyczne (UTF-8).
