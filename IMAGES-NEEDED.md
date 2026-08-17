# Brakujące zdjęcia — do uzupełnienia przed publikacją

`agnieszkakorach.png` jest już na serwerze i jest wpięte w kod (zdjęcie na
stronie głównej i na profilu eksperta). Logo firmy (`logo.png`) jest wpięte
w nagłówek i stopkę **każdej** podstrony w wersji odpornej na brak pliku: jeśli
plik istnieje pod `/assets/img/logo.png`, wyświetli się prawdziwe logo; jeśli
go jeszcze nie ma, strona pokazuje dotychczasowy zastępczy znak (kropki) —
nic się nie psuje, wystarczy wgrać plik, żeby logo pojawiło się automatycznie
wszędzie, bez zmian w kodzie.

Poniżej pozostałe pliki, które warto dodać do `/assets/img/`, żeby strona
wyglądała w pełni dopracowanie (wszystkie mają bezpieczny placeholder, więc
strona działa poprawnie także bez nich):

| Plik | Wymiary (zalecane) | Gdzie się pojawia | Opis |
|---|---|---|---|
| `logo.png` | np. 400×90 px (pozioma wersja z napisem) lub kwadrat, tło przezroczyste | Nagłówek i stopka każdej podstrony + dane strukturalne (Schema.org) | Prawdziwe logo firmy — po wgraniu zastąpi automatycznie zastępczy znak w nawigacji |
| `og-cover.jpg` | 1200×630 px | Podgląd linku (Facebook, LinkedIn, WhatsApp, komunikatory) na stronie głównej, blogu, o firmie, kontakcie | Ogólna grafika promocyjna firmy z logo i hasłem |
| `hero-szkolenie-pracownikow.jpg` | min. 760×950 px (proporcja ok. 4:5) | Strona główna — duże zdjęcie w sekcji hero | Zdjęcie zespołu/szkolenia w biurze, dobrej jakości, poziomo kadrowane pod górę |
| `romuald-korach.jpg` | min. 560×560 px (kwadrat) | Profil eksperta | Profesjonalne zdjęcie portretowe Romualda Koracha |
| `og-szkolenie-ai.jpg` | 1200×630 px | Podgląd linku — strona „Szkolenia z AI dla pracowników" i powiązany artykuł na blogu | Grafika tematyczna AI/szkolenie |
| `og-szkolenie-managera.jpg` | 1200×630 px | Podgląd linku — strona „Szkolenie dla managera" | Grafika tematyczna zarządzanie zespołem |
| `og-akademia-asystentek.jpg` | 1200×630 px | Podgląd linku — Akademia Asystentek i powiązany artykuł na blogu | Grafika tematyczna praca asystentki/biura |
| `og-odpornosc-psychiczna.jpg` | 1200×630 px | Podgląd linku — strona „Odporność psychiczna" | Grafika tematyczna dobrostan/psychologia |
| `karta-ai-codzienna-praca.jpg` | min. 640×360 px (proporcja 16:9) | Strona główna — karta „AI w codziennej pracy" w sekcji szkoleń otwartych | Zdjęcie ze szkolenia z AI (laptop, warsztat, praca zespołowa) |
| `karta-odpornosc-psychiczna.jpg` | min. 640×360 px (proporcja 16:9) | Strona główna — karta „Odporność psychiczna pod presją" w sekcji szkoleń otwartych | Zdjęcie ze szkolenia z odporności psychicznej |

## Favicon
Plik `assets/img/favicon.svg` już istnieje (prosty, wektorowy znak marki) —
działa od razu we wszystkich nowoczesnych przeglądarkach. Jeśli chcesz użyć
prawdziwego logo firmy jako ikony karty, podmień ten plik na wersję SVG
swojego logo (zachowaj tę samą nazwę pliku).

## Jak dodać zdjęcia na Zenbox
1. Zaloguj się do panelu Zenbox → **Menedżer plików** (lub połącz się przez FTP/SFTP).
2. Przejdź do katalogu `public_html/assets/img/` (lub odpowiadającego katalogu głównego domeny).
3. Wgraj pliki o nazwach dokładnie takich, jak w tabeli powyżej.
4. Odśwież stronę — obrazki podłączą się automatycznie, bez potrzeby edycji kodu.

Jeśli wolisz inne nazwy plików, daj znać — podmienię odwołania w kodzie.
