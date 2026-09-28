# Instalacja sklepu bydopamina.pl — krok po kroku (ok. 30–60 minut)

Potrzebujesz dwóch plików z folderu `dist/`:
- **`bydopamina-child.zip`** – motyw (wygląd sklepu, zabezpieczenia, funkcje),
- **`bydopamina-setup.zip`** – kreator, który jednym kliknięciem konfiguruje sklep.

Oraz pliku **`elementor-pro.zip`** z Twojego konta na elementor.com (licencja płatna).

---

## Krok 1. Hosting z WordPressem

Wybierz hosting z **automatyczną instalacją WordPressa, darmowym SSL, codziennymi kopiami i PHP 8.2+**
(np. hosting zarządzany WordPress). W panelu hostingu:
1. Podepnij domenę **bydopamina.pl** i włącz **certyfikat SSL** (https).
2. Kliknij „Zainstaluj WordPress” (język: polski). Zapisz login i hasło administratora (min. 16 znaków).

## Krok 2. Motyw i wtyczki (panel WordPressa → `bydopamina.pl/wp-admin`)

1. **Wygląd → Motywy → Dodaj nowy** → wyszukaj **Hello Elementor** → *Zainstaluj* (nie włączaj).
2. **Wygląd → Motywy → Dodaj nowy → Wyślij motyw** → wybierz `bydopamina-child.zip` → *Zainstaluj* → **Włącz**.
3. **Wtyczki → Dodaj nową** → zainstaluj i włącz: **WooCommerce**, **Elementor**.
   (Kreator WooCommerce możesz pominąć – kreator bydopamina ustawi wszystko sam.)
4. **Wtyczki → Dodaj nową → Wyślij wtyczkę** → `elementor-pro.zip` → *Zainstaluj* → *Włącz* → aktywuj licencję.
5. **Wtyczki → Dodaj nową → Wyślij wtyczkę** → `bydopamina-setup.zip` → *Zainstaluj* → *Włącz*.

## Krok 3. Jedno kliknięcie

**Narzędzia → bydopamina Setup** → sprawdź, czy wszystkie wymagania mają ✅ → **„Uruchom konfigurację sklepu”**.

Kreator zrobi automatycznie:

| Co | Szczegóły |
|---|---|
| WordPress | język polski, strefa czasowa Warszawa, adresy `/nazwa-strony/`, wyłączona rejestracja WP i komentarze, usunięte przykładowe treści |
| WooCommerce | Polska, PLN („129,00 zł”), zakupy bez zakładania konta, dodawanie do koszyka bez przeładowania, **opinie tylko od kupujących**, adresy `/produkt/…`, `/kategoria-produktu/…` |
| Kategorie | Kolczyki, Naszyjniki, Bransoletki, Pierścionki, Zestawy, Prezenty |
| Atrybuty | Kolor (18), Materiał (8), Kamień (17), Długość, Rozmiar |
| Tagi | bestseller, promocja, nastroj-radosc … nastroj-swiezosc |
| Wysyłka | strefa Polska: Kurier 14,99 zł (zmień) + darmowa dostawa od 199 zł |
| Strony | Start (strona główna), Sklep, Koszyk, Zamówienie, Moje konto, Kontakt, Promocje, Ulubione, Rozmiarówka – **gotowe i opublikowane** |
| Szkice | Regulamin, Polityka prywatności/cookies, Dostawa i płatności, Zwroty, Odstąpienie od umowy, Deklaracja dostępności, O nas, FAQ… – **do uzupełnienia** |
| Elementor | header, stopka, karta produktu, sklep/kategoria, 404 – przypisane automatycznie; kolory globalne; fonty lokalne |
| Menu | Nowości · Biżuteria (podkategorie) · Bestsellery · Na prezent · Promocje |

Kreator można uruchomić ponownie – niczego nie zdubluje.

## Krok 4. To robisz Ty (tego nie da się zautomatyzować)

1. **Produkty** (Produkty → Dodaj nowy): zdjęcia 1:1 na jasnym tle (drugie zdjęcie = na modelce), cena,
   kategoria, atrybuty Kolor / Materiał / Kamień, tag `bestseller` lub `promocja`, pole własne `bd_subtitle` (np. „z perłą, stal złocona”),
   w *Produktach powiązanych*: **Dosprzedaż** (do „Dobierz komplet”) i **Sprzedaż krzyżowa** (do mini-koszyka).
2. **Zdjęcia na stronie głównej**: Strony → Start → *Edytuj w Elementorze* → slider (3 slajdy), banery, newsletter.
   Miniatury kategorii: Produkty → Kategorie.
3. **Płatności**: wtyczka operatora (Przelewy24 lub PayU – BLIK, karty, Apple Pay) + umowa z operatorem.
4. **Dostawa**: wtyczka **InPost** (Paczkomaty) i ewentualnie DPD; popraw koszt kuriera w WooCommerce → Ustawienia → Wysyłka.
5. **Treści prawne**: uzupełnij i **opublikuj** szkice (Regulamin, Polityki, Zwroty, Odstąpienie od umowy, Deklaracja dostępności).
   Wzory najlepiej od prawnika lub z serwisu z regulaminami dla sklepów.
6. **Obowiązkowe wtyczki**:
   - *Omnibus — show the lowest price* (najniższa cena z 30 dni przy promocjach),
   - baner cookies z Consent Mode (np. *Complianz*),
   - bezpieczeństwo z logowaniem dwuetapowym (*Wordfence* lub *Two-Factor*),
   - kopie zapasowe poza serwerem (*UpdraftPlus*), jeśli hosting ich nie robi,
   - *TI WooCommerce Wishlist* (serduszka / Ulubione) i *Variation Swatches for WooCommerce* (kolory jako przyciski).
7. **Bezpieczeństwo serwera**: przejdź listę w `docs/BEZPIECZENSTWO.md` (część z nich zrobi za Ciebie hosting).
8. **Test zakupu**: złóż zamówienie testowe (BLIK testowy / tryb sandbox operatora), sprawdź maile i fakturę.

---

## Dla hostingu z dostępem SSH (WP-CLI) – wszystko jednym poleceniem

```bash
# w katalogu WordPressa; elementor-pro.zip połóż obok
bash /ścieżka/do/bydopamina/tools/install.sh /ścieżka/do/bydopamina/dist /ścieżka/do/elementor-pro.zip
```
Skrypt `tools/install.sh` instaluje motyw, WooCommerce, Elementor, Elementor Pro, zalecane darmowe wtyczki
i uruchamia `wp bydopamina setup`.

## Co zostało sprawdzone przed oddaniem
Kreator i motyw zostały uruchomione na świeżej instalacji WordPressa z WooCommerce (z plików ZIP):
wszystkie kroki kończą się bez błędów, powtórne uruchomienie niczego nie duplikuje, strony odpowiadają
(start, sklep, koszyk, konto, kontakt, promocje, 404), karty produktów pokazują podtytuł, etykiety, pole promocji,
cenę zestawu i kropki kolorów, a „Dobierz komplet” i „Pasuje do tego” w mini-koszyku działają.
**Z Elementorem Pro kreator nie był testowany** (płatna wtyczka niedostępna w środowisku testowym) –
po imporcie otwórz stronę główną i szablony w Elementorze i sprawdź, czy wszystko wygląda jak na podglądzie.
