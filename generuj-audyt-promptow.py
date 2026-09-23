# -*- coding: utf-8 -*-
"""Arkusz do audytu widoczności marki w ChatGPT, Perplexity i Claude."""
from openpyxl import Workbook
from openpyxl.styles import Font, PatternFill, Alignment, Border, Side
from openpyxl.worksheet.datavalidation import DataValidation
from openpyxl.utils import get_column_letter

PLIK = 'audyt-promptow-office-influencers.xlsx'

GRANAT = '0B2545'
ZOLTY = 'FFF2CC'
TINT = 'EBF2FA'

naglowek = Font(name='Arial', size=10, bold=True, color='FFFFFF')
tresc = Font(name='Arial', size=10)
tresc_b = Font(name='Arial', size=10, bold=True)
tytul = Font(name='Arial', size=13, bold=True, color=GRANAT)
szary = Font(name='Arial', size=9, color='616C7E')
tlo_naglowka = PatternFill('solid', fgColor=GRANAT)
tlo_zolte = PatternFill('solid', fgColor=ZOLTY)
tlo_tint = PatternFill('solid', fgColor=TINT)
zawijaj = Alignment(wrap_text=True, vertical='top')
gora = Alignment(vertical='top')
ramka = Border(*[Side(style='thin', color='D6DBE3')] * 4)

PROMPTY = [
    ('Markowe', 'Czym zajmuje się Office Influencers?'),
    ('Markowe', 'Kim jest Agnieszka Korach i czym się zajmuje zawodowo?'),
    ('Markowe', 'Jakie szkolenia prowadzi Office Influencers?'),
    ('Markowe', 'Ile kosztuje szkolenie dla asystentki zarządu w Office Influencers?'),
    ('Markowe', 'Czy Office Influencers to wiarygodna firma szkoleniowa?'),
    ('Markowe', 'Jakie są opinie o szkoleniach Agnieszki Korach?'),
    ('Markowe', 'Na czym polega szkolenie „Asystentka – Partner w Zarządzaniu. Myśl jak Szef"?'),
    ('Markowe', 'Dla kogo jest program „Myśl jak Szef" dla asystentek zarządu?'),

    ('Kategoryjne', 'Najlepsze szkolenia dla asystentek zarządu w Polsce'),
    ('Kategoryjne', 'Kto prowadzi szkolenia dla Executive Assistants w Polsce?'),
    ('Kategoryjne', 'Szkolenia dla office managerek, jakie firmy je oferują?'),
    ('Kategoryjne', 'Kursy rozwojowe dla asystentek zarządu, co polecacie?'),
    ('Kategoryjne', 'Firmy szkoleniowe specjalizujące się we wsparciu zarządu i pracy asystentek'),
    ('Kategoryjne', 'Szkolenie z AI dla asystentek zarządu, gdzie się zapisać?'),
    ('Kategoryjne', 'Szkolenia in-house dla zespołu asystentek, kto je prowadzi w Warszawie?'),
    ('Kategoryjne', 'Kto szkoli asystentki zarządu z samodzielności i podejmowania decyzji?'),
    ('Kategoryjne', 'Otwarte szkolenia dla asystentek zarządu, terminy 2026'),
    ('Kategoryjne', 'Szkolenia dla recepcji i office managementu w Polsce'),
    ('Kategoryjne', 'Kto w Polsce uczy asystentki zarządu korzystania z AI w pracy?'),
    ('Kategoryjne', 'Polecane szkolenia dla asystentki prezesa'),

    ('Porównawcze', 'Office Influencers a [wpisz konkurenta z kategorii 2], czym się różnią?'),
    ('Porównawcze', 'Alternatywy dla szkoleń [wpisz konkurenta z kategorii 2] dla asystentek zarządu'),
    ('Porównawcze', 'Czym różnią się szkolenia Office Influencers od innych szkoleń dla asystentek?'),
    ('Porównawcze', 'Co wybrać dla zespołu asystentek: szkolenie otwarte czy in-house?'),
    ('Porównawcze', 'Które szkolenie dla asystentki zarządu daje najwięcej praktyki, a nie teorii?'),
    ('Porównawcze', 'Czy dla asystentki zarządu lepszy jest kurs online czy szkolenie stacjonarne?'),

    ('Problemowe', 'Jak asystentka zarządu może zwiększyć swoją samodzielność w pracy?'),
    ('Problemowe', 'Co zrobić, gdy przełożony nie deleguje decyzji asystentce?'),
    ('Problemowe', 'Jak przygotować rekomendację dla zarządu, żeby szybko podjął decyzję?'),
    ('Problemowe', 'Jak asystentka ma rozstrzygać sprzeczne priorytety dwóch przełożonych?'),
    ('Problemowe', 'Jak przekonać przełożonego, żeby sfinansował mi szkolenie?'),
    ('Problemowe', 'Jak napisać przełożonemu uzasadnienie udziału w szkoleniu?'),
    ('Problemowe', 'Ile powinno kosztować dwudniowe szkolenie biznesowe w Warszawie?'),
    ('Problemowe', 'Jak rozwijać się zawodowo na stanowisku asystentki zarządu?'),
    ('Problemowe', 'Jakie kompetencje są dziś najważniejsze dla Executive Assistant?'),
    ('Problemowe', 'Jak asystentka zarządu może wykorzystać AI w codziennej pracy?'),
    ('Problemowe', 'Co zrobić, żeby sprawy nie wracały do szefa po kolejne akceptacje?'),
    ('Problemowe', 'Jak asystentka może przejąć część spraw zarządu, nie przekraczając kompetencji?'),
    ('Problemowe', 'Czy warto inwestować w szkolenia dla asystentek zarządu?'),
    ('Problemowe', 'Jak zmierzyć efekty szkolenia dla asystentki zarządu?'),
]

KOLUMNY_POMIARU = [
    ('Data', 12), ('Silnik', 14), ('Tryb', 18), ('ID promptu', 11),
    ('Kategoria', 14), ('Marka się pojawiła', 12), ('Cytowanie z linkiem', 12),
    ('Wskazane źródła', 34), ('Inne wymienione marki', 30),
    ('Informacja poprawna', 12), ('Uwagi', 30),
]

WIERSZY_POMIARU = 300

wb = Workbook()

# ---------------------------------------------------------------- Instrukcja
ws = wb.active
ws.title = 'Instrukcja'
ws.sheet_view.showGridLines = False
ws.column_dimensions['A'].width = 3
ws.column_dimensions['B'].width = 104

linie = [
    ('t', 'Audyt widoczności w modelach językowych'),
    ('s', 'Office Influencers, Agnieszka Korach. Pomiar powtarzany co miesiąc.'),
    ('', ''),
    ('h', 'Po co to jest'),
    ('p', 'Punktowe sprawdzenie „czy ChatGPT o mnie wie" nic nie mówi, bo odpowiedzi zmieniają się '
          'między silnikami, sesjami i dniami. Wartość ma dopiero ten sam zestaw pytań zadawany '
          'regularnie i zapisywany. Dopiero trend coś znaczy.'),
    ('', ''),
    ('h', 'Jak to prowadzić'),
    ('p', '1. Zakładka „Prompty" to stały zestaw 40 pytań. Zadawaj je dosłownie, bez przerabiania. '
          'Drobna zmiana sformułowania daje inną odpowiedź i psuje porównywalność.'),
    ('p', '2. Każde pytanie zadaj w świeżej rozmowie. Historia wątku zmienia odpowiedź. '
          'Nigdy nie mierz w wątku, w którym wcześniej poprawiałaś model.'),
    ('p', '3. Każdy silnik mierz osobno: ChatGPT, Perplexity, Claude. To osobne rynki. '
          'Pokrycie domen cytowanych przez ChatGPT i Perplexity to około 11%, więc marka widoczna '
          'w jednym bywa zupełnie nieobecna w drugim.'),
    ('p', '4. Tam, gdzie się da, zadaj pytanie w dwóch trybach: z wyszukiwaniem i bez. '
          'Jeśli odpowiedź jest dobra tylko z wyszukiwaniem, problem siedzi w wiedzy z treningu '
          'i realnie da się pracować wyłącznie nad źródłami w sieci. To akurat dobra wiadomość, '
          'bo ta warstwa naprawia się w tygodniach, nie w latach.'),
    ('p', '5. Wyniki wpisuj w zakładkę „Pomiar". Zakładka „Wyniki" przeliczy się sama.'),
    ('', ''),
    ('h', 'Co jest najważniejsze w wynikach'),
    ('p', 'Kolumna „Wskazane źródła" to Twoje zlecenie robocze. Jeśli model cytuje pięć stron '
          'i żadna nie jest Twoja, to właśnie na tych stronach masz być obecna. Nie walcz '
          'o bezpośrednie cytowanie własnej witryny, tylko o obecność w źródłach, które '
          'ten silnik i tak cytuje w Twojej kategorii.'),
    ('p', 'Kolumna „Inne wymienione marki" buduje Ci mapę konkurencji widzianą oczami modelu. '
          'Po pierwszym pomiarze wstaw te nazwy w prompty porównawcze P021 i P022, '
          'gdzie czeka nawias do uzupełnienia.'),
    ('', ''),
    ('h', 'Jak czytać typowe wyniki'),
    ('p', 'Jesteś w markowych, nie ma Cię w kategoryjnych: model wie, kim jesteś, gdy zapytać wprost, '
          'ale nie kojarzy Cię z kategorią. Praca idzie w zestawienia branżowe i ko-cytowanie.'),
    ('p', 'Jesteś wspominana, ale bez linku: to najczęstszy wynik. Wzmianka buduje świadomość, '
          'nie daje ruchu. Droga wiedzie przez źródła, które silnik cytuje.'),
    ('p', 'Model myli Cię z inną firmą: problem tożsamości. Priorytet to jednakowa nazwa, opis '
          'i kategoria wszędzie, gdzie występujesz.'),
    ('', ''),
    ('h', 'Czego nie robimy'),
    ('p', 'Nie kupujemy wzmianek, nie zakładamy kont, żeby polecić samą siebie, nie zamawiamy '
          'sztucznych dyskusji. Poza kwestią uczciwości: systemy antyspamowe to wychwytują, '
          'a wykryta manipulacja kosztuje więcej, niż dałaby.'),
    ('', ''),
    ('h', 'Żółte pola wypełniasz, białe liczą się same'),
    ('p', 'W zakładce „Pomiar" wypełniasz kolumny od A do D oraz od F do K. Kolumna E '
          'podstawia kategorię sama. Zakładka „Wyniki" liczy się w całości sama.'),
]

w = 2
for rodzaj, txt in linie:
    if txt:
        k = ws.cell(row=w, column=2, value=txt)
        if rodzaj == 't':
            k.font = tytul
        elif rodzaj == 's':
            k.font = szary
        elif rodzaj == 'h':
            k.font = tresc_b
        else:
            k.font = tresc
            k.alignment = zawijaj
            ws.row_dimensions[w].height = 15 * (1 + len(txt) // 100)
    w += 1

w += 1
ws.cell(row=w, column=2, value='Przykładowy wiersz pomiaru, tak wypełniony wiersz ma wyglądać:').font = tresc_b
w += 1
for i, (nazwa, _) in enumerate(KOLUMNY_POMIARU):
    k = ws.cell(row=w, column=2 + i, value=nazwa)
    k.font = naglowek
    k.fill = tlo_naglowka
    k.alignment = zawijaj
przyklad = ['2026-10-05', 'ChatGPT', 'z wyszukiwaniem', 'P009', 'Kategoryjne', 'nie', 'nie',
            'training.pl, gazeta-branzowa.pl/ranking-szkolen', 'Firma A, Firma B, Firma C',
            'nie dotyczy', 'wymienia 5 firm, mnie wśród nich nie ma']
w += 1
for i, v in enumerate(przyklad):
    k = ws.cell(row=w, column=2 + i, value=v)
    k.font = tresc
    k.alignment = zawijaj
    k.border = ramka
for i in range(len(KOLUMNY_POMIARU)):
    ws.column_dimensions[get_column_letter(3 + i)].width = 18

# ------------------------------------------------------------------- Prompty
wp = wb.create_sheet('Prompty')
wp.sheet_view.showGridLines = False
for i, (nazwa, szer) in enumerate([('ID', 9), ('Kategoria', 16), ('Prompt', 88)], start=1):
    k = wp.cell(row=1, column=i, value=nazwa)
    k.font = naglowek
    k.fill = tlo_naglowka
    wp.column_dimensions[get_column_letter(i)].width = szer
for n, (kat, tekst) in enumerate(PROMPTY, start=1):
    r = n + 1
    wp.cell(row=r, column=1, value='P%03d' % n).font = tresc
    wp.cell(row=r, column=2, value=kat).font = tresc
    k = wp.cell(row=r, column=3, value=tekst)
    k.font = tresc
    k.alignment = zawijaj
    for c in range(1, 4):
        wp.cell(row=r, column=c).border = ramka
        wp.cell(row=r, column=c).alignment = gora
    if kat in ('Kategoryjne', 'Problemowe'):
        for c in range(1, 4):
            wp.cell(row=r, column=c).fill = tlo_tint
wp.freeze_panes = 'A2'
r = len(PROMPTY) + 3
k = wp.cell(row=r, column=3, value='Podświetlone kategorie są najważniejsze. Kategoryjne pokazują, '
            'czy w ogóle istniejesz dla kogoś, kto nie zna Twojej nazwy. Problemowe pokazują, czy jesteś '
            'obecna na etapie, gdy klientka szuka rozwiązania, a nie firmy. Tam zapada decyzja.')
k.font = szary
k.alignment = zawijaj
wp.row_dimensions[r].height = 45

# -------------------------------------------------------------------- Pomiar
wm = wb.create_sheet('Pomiar')
for i, (nazwa, szer) in enumerate(KOLUMNY_POMIARU, start=1):
    k = wm.cell(row=1, column=i, value=nazwa)
    k.font = naglowek
    k.fill = tlo_naglowka
    k.alignment = zawijaj
    wm.column_dimensions[get_column_letter(i)].width = szer
wm.row_dimensions[1].height = 30
wm.freeze_panes = 'A2'

for r in range(2, WIERSZY_POMIARU + 2):
    for c in range(1, len(KOLUMNY_POMIARU) + 1):
        k = wm.cell(row=r, column=c)
        k.font = tresc
        k.border = ramka
        k.alignment = gora
        if c != 5:
            k.fill = tlo_zolte
    wm.cell(row=r, column=5, value=(
        '=IFERROR(INDEX(Prompty!$B$2:$B$%d,MATCH($D%d,Prompty!$A$2:$A$%d,0)),"")'
        % (len(PROMPTY) + 1, r, len(PROMPTY) + 1)))

listy = {
    'B': '"ChatGPT,Perplexity,Claude"',
    'C': '"z wyszukiwaniem,bez wyszukiwania"',
    'F': '"tak,nie"',
    'G': '"tak,nie"',
    'J': '"tak,nie,nie dotyczy"',
}
for kol, formula in listy.items():
    dv = DataValidation(type='list', formula1=formula, allow_blank=True)
    wm.add_data_validation(dv)
    dv.add('%s2:%s%d' % (kol, kol, WIERSZY_POMIARU + 1))

dv_id = DataValidation(type='list', formula1='=Prompty!$A$2:$A$%d' % (len(PROMPTY) + 1), allow_blank=True)
wm.add_data_validation(dv_id)
dv_id.add('D2:D%d' % (WIERSZY_POMIARU + 1))

# -------------------------------------------------------------------- Wyniki
ww = wb.create_sheet('Wyniki')
ww.sheet_view.showGridLines = False
ww.column_dimensions['A'].width = 26
for c in 'BCDE':
    ww.column_dimensions[c].width = 17

ww['A1'] = 'Wyniki audytu'
ww['A1'].font = tytul
ww['A2'] = 'Liczy się samo z zakładki „Pomiar". Sama wartość nic nie znaczy, znaczenie ma zmiana w czasie.'
ww['A2'].font = szary

OST = WIERSZY_POMIARU + 1
SILNIKI = ['ChatGPT', 'Perplexity', 'Claude']


def blok(wiersz, tytul_bloku, warunek=None):
    k = ww.cell(row=wiersz, column=1, value=tytul_bloku)
    k.font = tresc_b
    for i, nazwa in enumerate(['Silnik', 'Pomiarów', 'Obecność', 'Cytowanie', 'Poprawność']):
        c = ww.cell(row=wiersz + 1, column=1 + i, value=nazwa)
        c.font = naglowek
        c.fill = tlo_naglowka
    for j, silnik in enumerate(SILNIKI):
        r = wiersz + 2 + j
        ww.cell(row=r, column=1, value=silnik).font = tresc
        extra = ',Pomiar!$E$2:$E$%d,"%s"' % (OST, warunek) if warunek else ''
        baza = 'COUNTIFS(Pomiar!$B$2:$B$%d,$A%d%s)' % (OST, r, extra)
        ww.cell(row=r, column=2, value='=%s' % baza).font = tresc
        for kol, litera in ((3, 'F'), (4, 'G'), (5, 'J')):
            wzor = ('=IFERROR(COUNTIFS(Pomiar!$B$2:$B$%d,$A%d%s,Pomiar!$%s$2:$%s$%d,"tak")/%s,"")'
                    % (OST, r, extra, litera, litera, OST, baza))
            c = ww.cell(row=r, column=kol, value=wzor)
            c.font = tresc
            c.number_format = '0.0%;-;-'
        for c in range(1, 6):
            ww.cell(row=r, column=c).border = ramka
    return wiersz + 2 + len(SILNIKI) + 1


w = blok(4, 'Wszystkie prompty razem')
for kat in ['Markowe', 'Kategoryjne', 'Porównawcze', 'Problemowe']:
    w = blok(w, 'Tylko prompty: ' + kat, kat)

ww.cell(row=w, column=1, value='Obecność: w ilu procentach pomiarów marka w ogóle się pojawiła.').font = szary
ww.cell(row=w + 1, column=1, value='Cytowanie: w ilu procentach pojawiła się z odnośnikiem. To zwykle dużo mniej niż obecność.').font = szary
ww.cell(row=w + 2, column=1, value='Poprawność: w ilu procentach informacja o marce była prawdziwa.').font = szary

wb.save(PLIK)
print('zapisano:', PLIK)
print('promptów:', len(PROMPTY), '| wierszy na pomiary:', WIERSZY_POMIARU)
