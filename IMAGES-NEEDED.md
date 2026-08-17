# Brakujące zdjęcia — do uzupełnienia przed publikacją

Kod strony jest gotowy, ale nie mam dostępu do prawdziwych plików graficznych ze
starej strony WordPress (katalog `wp-content/uploads/`). Wszystkie miejsca na
zdjęcia mają bezpieczne zabezpieczenie „na wypadek braku pliku" (widoczny
placeholder zamiast złamanego obrazka), więc strona **nie wygląda na zepsutą**
nawet bez tych plików — ale wygląda dużo lepiej i wiarygodniej z prawdziwymi
zdjęciami. Poniżej pełna lista plików, które warto dodać do `/assets/img/`.

Format nazw plików jest już wpięty w kod — wystarczy wgrać plik o dokładnie
takiej nazwie na serwer Zenbox, do folderu `assets/img/`, żeby zaczął się
wyświetlać automatycznie (bez zmian w HTML).

| Plik | Wymiary (zalecane) | Gdzie się pojawia | Opis |
|---|---|---|---|
| `logo.png` | 512×512 px, tło przezroczyste | Dane strukturalne (Schema.org) na każdej stronie | Logo firmy w wersji kwadratowej — używane przez Google do wyświetlania w wynikach i Knowledge Panel |
| `og-cover.jpg` | 1200×630 px | Podgląd linku (Facebook, LinkedIn, WhatsApp, komunikatory) na stronie głównej, blogu, o firmie, kontakcie | Ogólna grafika promocyjna firmy z logo i hasłem |
| `hero-szkolenie-pracownikow.jpg` | min. 760×950 px (proporcja ok. 4:5) | Strona główna — duże zdjęcie w sekcji hero | Zdjęcie zespołu/szkolenia w biurze, dobrej jakości, poziomo kadrowane pod górę |
| `agnieszka-korach.jpg` | min. 560×560 px (kwadrat) | Strona główna (CTA), profil eksperta | Profesjonalne zdjęcie portretowe Agnieszki Korach |
| `romuald-korach.jpg` | min. 560×560 px (kwadrat) | Profil eksperta | Profesjonalne zdjęcie portretowe Romualda Koracha |
| `og-szkolenie-ai.jpg` | 1200×630 px | Podgląd linku — strona „Szkolenia z AI dla pracowników" i powiązany artykuł na blogu | Grafika tematyczna AI/szkolenie |
| `og-szkolenie-managera.jpg` | 1200×630 px | Podgląd linku — strona „Szkolenie dla managera" | Grafika tematyczna zarządzanie zespołem |
| `og-akademia-asystentek.jpg` | 1200×630 px | Podgląd linku — Akademia Asystentek i powiązany artykuł na blogu | Grafika tematyczna praca asystentki/biura |
| `og-odpornosc-psychiczna.jpg` | 1200×630 px | Podgląd linku — strona „Odporność psychiczna" | Grafika tematyczna dobrostan/psychologia |

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
