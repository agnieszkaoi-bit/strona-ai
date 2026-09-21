const fs = require('fs');
const {
  Document, Packer, Paragraph, TextRun, HeadingLevel, AlignmentType,
  Table, TableRow, TableCell, WidthType, ShadingType, BorderStyle,
  ImageRun, Header, Footer, PageNumber, LevelFormat, convertInchesToTwip,
} = require('docx');

// --- paleta ze strony ---
const GRANAT = '0B2545';
const GRANAT_JASNY = '1B5390';
const SZARY = '616C7E';
const LINIA = 'E5E9F0';
const TINT = 'EBF2FA';
const CZCIONKA = 'Calibri';

const SZER = 9360;            // szerokość kolumny tekstu w DXA (A4, marginesy 1")
const BRAK = { style: BorderStyle.NONE, size: 0, color: 'FFFFFF' };
const BRAK_RAMKI = { top: BRAK, bottom: BRAK, left: BRAK, right: BRAK,
                     insideHorizontal: BRAK, insideVertical: BRAK };

// --- skróty do akapitów ---
const t = (text, o = {}) => new TextRun({ text, font: CZCIONKA, ...o });

const p = (text, o = {}) => new Paragraph({
  spacing: { after: o.after ?? 120, line: 276 },
  alignment: o.align,
  indent: o.indent,
  children: [t(text, { size: o.size ?? 21, color: o.color ?? '16202E', bold: o.bold, italics: o.italics })],
});

const pusty = (h = 120) => new Paragraph({ spacing: { after: h }, children: [] });

const h1 = (text) => new Paragraph({
  heading: HeadingLevel.HEADING_1,
  spacing: { before: 360, after: 200 },
  children: [t(text, { size: 30, bold: true, color: GRANAT })],
});

const h2 = (text) => new Paragraph({
  heading: HeadingLevel.HEADING_2,
  spacing: { before: 280, after: 140 },
  children: [t(text, { size: 24, bold: true, color: GRANAT })],
});

const nadtytul = (text) => new Paragraph({
  spacing: { before: 320, after: 60 },
  children: [t(text.toUpperCase(), { size: 16, bold: true, color: GRANAT_JASNY, characterSpacing: 40 })],
});

const kreska = () => new Paragraph({
  spacing: { before: 60, after: 180 },
  border: { bottom: { style: BorderStyle.SINGLE, size: 6, color: LINIA, space: 1 } },
  children: [],
});

const punkt = (text) => new Paragraph({
  numbering: { reference: 'kropki', level: 0 },
  spacing: { after: 70, line: 276 },
  children: [t(text, { size: 21, color: '16202E' })],
});

// --- komórki tabel ---
const kom = (dzieci, o = {}) => new TableCell({
  width: { size: o.szer, type: WidthType.DXA },
  margins: { top: 110, bottom: 110, left: 140, right: 140 },
  shading: o.tlo ? { type: ShadingType.CLEAR, fill: o.tlo, color: 'auto' } : undefined,
  verticalAlign: 'center',
  children: dzieci,
});

const komTekst = (text, o = {}) => kom(
  [new Paragraph({
    spacing: { after: 0, line: 264 },
    alignment: o.align,
    children: [t(text, { size: o.size ?? 20, bold: o.bold, color: o.color ?? '16202E' })],
  })],
  o
);

// ===================== TREŚĆ =====================

const logo = fs.readFileSync('/home/user/strona-ai/img/office-influencers-logo.png');

const naglowekStrony = new Header({
  children: [
    new Paragraph({
      spacing: { after: 40 },
      children: [new ImageRun({
        data: logo, type: 'png',
        transformation: { width: 118, height: 38 },
      })],
    }),
    new Paragraph({
      spacing: { after: 0 },
      border: { bottom: { style: BorderStyle.SINGLE, size: 6, color: LINIA, space: 6 } },
      children: [],
    }),
  ],
});

const stopkaStrony = new Footer({
  children: [
    new Paragraph({
      spacing: { before: 60 },
      border: { top: { style: BorderStyle.SINGLE, size: 6, color: LINIA, space: 6 } },
      children: [],
    }),
    new Paragraph({
      alignment: AlignmentType.CENTER,
      children: [t('ARK Consulting Agnieszka Korach · office@officeinfluencers.pl · +48 22 435 70 02 · strona ', { size: 16, color: SZARY }),
                 new TextRun({ children: [PageNumber.CURRENT], font: CZCIONKA, size: 16, color: SZARY }),
                 t(' z ', { size: 16, color: SZARY }),
                 new TextRun({ children: [PageNumber.TOTAL_PAGES], font: CZCIONKA, size: 16, color: SZARY })],
    }),
  ],
});

// --- strona tytułowa ---
const tytul = [
  pusty(600),
  new Paragraph({
    spacing: { after: 100 },
    children: [t('OFERTA SZKOLENIA OTWARTEGO', { size: 18, bold: true, color: GRANAT_JASNY, characterSpacing: 60 })],
  }),
  new Paragraph({
    spacing: { after: 60 },
    children: [t('Asystentka – Partner w Zarządzaniu I.', { size: 44, bold: true, color: GRANAT })],
  }),
  new Paragraph({
    spacing: { after: 260 },
    children: [t('Myśl jak Szef', { size: 44, bold: true, color: GRANAT }),
               t('™', { size: 24, bold: true, color: GRANAT, superScript: true })],
  }),
  new Paragraph({
    spacing: { after: 320 },
    children: [t('Dwa dni warsztatów dla doświadczonych asystentek zarządu. Poznasz sposób myślenia osób zarządzających, zwiększysz swój wpływ i sprawczość. Każdą z tych kompetencji wzmocnisz narzędziami AI.',
      { size: 23, color: SZARY })],
  }),
];

const kartaFakty = new Table({
  width: { size: SZER, type: WidthType.DXA },
  columnWidths: [2400, 6960],
  borders: {
    top: { style: BorderStyle.SINGLE, size: 6, color: LINIA },
    bottom: { style: BorderStyle.SINGLE, size: 6, color: LINIA },
    left: { style: BorderStyle.SINGLE, size: 6, color: LINIA },
    right: { style: BorderStyle.SINGLE, size: 6, color: LINIA },
    insideHorizontal: { style: BorderStyle.SINGLE, size: 6, color: LINIA },
    insideVertical: BRAK,
  },
  rows: [
    ['Termin', '19–20 listopada 2026'],
    ['Miejsce', 'Warszawa, Centrum Biznesowe Ogrodowa 58, ul. Ogrodowa 58'],
    ['Godziny', 'I dzień 10.00–17.00 · II dzień 9.00–16.00'],
    ['Forma', 'Szkolenie stacjonarne, 2 dni'],
    ['Cena', '2 100 zł netto za osobę (2 583 zł brutto)'],
    ['Prowadzi', 'Agnieszka Korach'],
  ].map(([k, v]) => new TableRow({
    children: [
      komTekst(k, { szer: 2400, bold: true, color: GRANAT, tlo: TINT }),
      komTekst(v, { szer: 6960 }),
    ],
  })),
});

const miejsce = new Table({
  width: { size: SZER, type: WidthType.DXA },
  columnWidths: [2400, 6960],
  borders: {
    top: { style: BorderStyle.SINGLE, size: 6, color: LINIA },
    bottom: { style: BorderStyle.SINGLE, size: 6, color: LINIA },
    left: { style: BorderStyle.SINGLE, size: 6, color: LINIA },
    right: { style: BorderStyle.SINGLE, size: 6, color: LINIA },
    insideHorizontal: { style: BorderStyle.SINGLE, size: 6, color: LINIA },
    insideVertical: BRAK,
  },
  rows: [
    ['Data', '19–20 listopada 2026 (czwartek, piątek)'],
    ['Adres', 'Centrum Biznesowe Ogrodowa 58, ul. Ogrodowa 58, Warszawa'],
    ['I dzień', 'godz. 10.00–17.00'],
    ['II dzień', 'godz. 9.00–16.00'],
  ].map(([k, v]) => new TableRow({
    children: [
      komTekst(k, { szer: 2400, bold: true, color: GRANAT, tlo: TINT }),
      komTekst(v, { szer: 6960 }),
    ],
  })),
});

// --- program: moduły ---
const moduly = [
  ['1', 'Styl pracy i wartości osoby zarządzającej',
    ['patrzeć na sprawy z perspektywy osoby zarządzającej',
     'dopasowywać współpracę do stylu pracy i osobowości szefa_owej',
     'rozpoznawać wartości i priorytety po decyzjach',
     'działać dyskretnie i lojalnie w sytuacjach niejednoznacznych'],
    'Profil współpracy z szefem_ową.',
    'porządkowanie preferencji szefa_owej i analiza dylematów'],
  ['2', 'Określanie i definiowanie celu',
    ['odróżniać cel od zadania i od życzenia',
     'opisywać cel przez rezultat i kryteria sukcesu',
     'przekładać cele zarządu na własne priorytety',
     'uzgadniać z szefem_ową, po czym poznacie, że cel jest osiągnięty'],
    'Mapę celów na najbliższy kwartał.',
    'sprawdzanie, czy cel jest mierzalny, i rozbijanie go na kroki'],
  ['3', 'Komunikacja z szefem_ową i w jego/jej imieniu',
    ['przekazywać najważniejsze informacje w krótkim czasie',
     'dawać konstruktywny feedback',
     'pisać w imieniu zarządu we właściwym tonie'],
    'Ściągawkę komunikacji i szablon informacji dla szefa_owej.',
    'szkice, zmiana tonu i test odbioru komunikatu'],
];

const moduly2 = [
  ['4', 'Asertywność i skuteczność pod presją',
    ['chronić czas zarządu bez psucia relacji',
     'zgłaszać szefowi_owej ryzyka i zastrzeżenia',
     'zachować spokój i jakość pracy w sytuacjach kryzysowych'],
    'Zestaw technik asertywnych.',
    'trening trudnej rozmowy i trudnych wiadomości'],
  ['5', 'Delegowanie i poszerzanie wpływu',
    ['korzystać z poziomów delegowania',
     'przyjmować zadania z jasnym zakresem uprawnień',
     'stopniowo zwiększać zaufanie i samodzielność',
     'budować autorytet wśród kadry zarządzającej i zespołów'],
    'Mapę moich decyzji i plan poszerzania wpływu.',
    'delegowanie zadań AI z kontrolą wyniku, argumentacja i przewidywanie zastrzeżeń'],
  ['6', 'Podejmowanie decyzji',
    ['rozumieć, jak podejmuje decyzje Twój szef/Twoja szefowa',
     'przygotowywać właściwe rekomendacje'],
    'Szablon rekomendacji dla szefa/szefowej i plan wdrożenia na 90 dni.',
    'generowanie opcji i wyłapywanie tego, co mogło umknąć'],
];

function modul([nr, nazwa, uczysz, przygotujesz, ai]) {
  return [
    new Paragraph({
      spacing: { before: 260, after: 100 },
      children: [
        t(nr + '.  ', { size: 22, bold: true, color: GRANAT_JASNY }),
        t(nazwa, { size: 22, bold: true, color: GRANAT }),
      ],
    }),
    new Paragraph({
      spacing: { after: 60 },
      indent: { left: 340 },
      children: [t('Czego się nauczysz', { size: 18, bold: true, color: SZARY })],
    }),
    ...uczysz.map((u) => new Paragraph({
      numbering: { reference: 'kropki', level: 0 },
      spacing: { after: 50, line: 264 },
      children: [t(u, { size: 20 })],
    })),
    new Paragraph({
      spacing: { before: 110, after: 50 },
      indent: { left: 340 },
      children: [t('Co przygotujesz:  ', { size: 18, bold: true, color: SZARY }),
                 t(przygotujesz, { size: 20 })],
    }),
    new Paragraph({
      spacing: { after: 60 },
      indent: { left: 340 },
      children: [t('AI w tym module:  ', { size: 18, bold: true, color: SZARY }),
                 t(ai, { size: 20, italics: true, color: SZARY })],
    }),
  ];
}

function dzien(etykieta, podtytul) {
  return new Table({
    width: { size: SZER, type: WidthType.DXA },
    columnWidths: [SZER],
    borders: BRAK_RAMKI,
    rows: [new TableRow({
      children: [kom([
        new Paragraph({
          spacing: { after: 20 },
          children: [t(etykieta.toUpperCase(), { size: 16, bold: true, color: 'FFFFFF', characterSpacing: 60 })],
        }),
        new Paragraph({
          spacing: { after: 0 },
          children: [t(podtytul, { size: 26, bold: true, color: 'FFFFFF' })],
        }),
      ], { szer: SZER, tlo: GRANAT })],
    })],
  });
}

// --- cennik ---
const wierszeCen = [1, 2, 3, 4, 5].map((n) => {
  const netto = n <= 2 ? n * 2100 : 2 * 2100 + (n - 2) * 2100 * 0.95;
  const brutto = netto * 1.23;
  const zl = (v) => v.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/, '\u00A0') + ' zł';
  return new TableRow({
    children: [
      komTekst(n === 5 ? '5 osób i więcej' : (n === 1 ? '1 osoba' : n + ' osoby'), { szer: 2600 }),
      komTekst((n >= 3 ? 'tak, 5% od 3. osoby' : '—'), { szer: 2760, color: n >= 3 ? GRANAT_JASNY : SZARY }),
      komTekst((n === 5 ? 'od ' : '') + zl(netto), { szer: 2000, align: AlignmentType.RIGHT, bold: true }),
      komTekst((n === 5 ? 'od ' : '') + zl(brutto), { szer: 2000, align: AlignmentType.RIGHT, color: SZARY }),
    ],
  });
});

const cennik = new Table({
  width: { size: SZER, type: WidthType.DXA },
  columnWidths: [2600, 2760, 2000, 2000],
  borders: {
    top: { style: BorderStyle.SINGLE, size: 6, color: LINIA },
    bottom: { style: BorderStyle.SINGLE, size: 6, color: LINIA },
    left: { style: BorderStyle.SINGLE, size: 6, color: LINIA },
    right: { style: BorderStyle.SINGLE, size: 6, color: LINIA },
    insideHorizontal: { style: BorderStyle.SINGLE, size: 6, color: LINIA },
    insideVertical: { style: BorderStyle.SINGLE, size: 6, color: LINIA },
  },
  rows: [
    new TableRow({
      tableHeader: true,
      children: [
        komTekst('Liczba osób', { szer: 2600, bold: true, color: GRANAT, tlo: TINT }),
        komTekst('Rabat', { szer: 2760, bold: true, color: GRANAT, tlo: TINT }),
        komTekst('Razem netto', { szer: 2000, bold: true, color: GRANAT, tlo: TINT, align: AlignmentType.RIGHT }),
        komTekst('Razem brutto', { szer: 2000, bold: true, color: GRANAT, tlo: TINT, align: AlignmentType.RIGHT }),
      ],
    }),
    ...wierszeCen,
  ],
});

// ===================== DOKUMENT =====================

const doc = new Document({
  creator: 'ARK Consulting Agnieszka Korach',
  title: 'Oferta szkolenia: Asystentka – Partner w Zarządzaniu I. Myśl jak Szef',
  description: 'Oferta dwudniowego szkolenia otwartego dla asystentek zarządu i Executive Assistants.',
  styles: {
    default: {
      document: { run: { font: CZCIONKA, size: 21, color: '16202E' } },
    },
  },
  numbering: {
    config: [{
      reference: 'kropki',
      levels: [{
        level: 0,
        format: LevelFormat.BULLET,
        text: '•',
        alignment: AlignmentType.LEFT,
        style: { paragraph: { indent: { left: 600, hanging: 240 } },
                 run: { color: GRANAT_JASNY } },
      }],
    }],
  },
  sections: [{
    properties: {
      page: {
        margin: { top: 1300, right: 1440, bottom: 1300, left: 1440 },
      },
    },
    headers: { default: naglowekStrony },
    footers: { default: stopkaStrony },
    children: [
      ...tytul,
      kartaFakty,
      pusty(400),
      p('Ofertę przygotowała Agnieszka Korach, Office Influencers · ARK Consulting Agnieszka Korach.', { size: 18, color: SZARY }),
      p('Oferta ważna do 18 listopada 2026 r. lub do wyczerpania miejsc.', { size: 18, color: SZARY }),

      // --- dla kogo ---
      nadtytul('Uczestniczki'),
      h1('Dla kogo jest to szkolenie'),
      p('To szkolenie dla asystentki zarządu i Executive Assistant z kilkuletnim stażem – nie kurs dla osób, które dopiero zaczynają.'),
      h2('Dla kogo jest'),
      punkt('Asystentka/Asystent osoby zarządzającej – zbudujesz współpracę opartą na zaufaniu i jasnym mandacie decyzyjnym.'),
      punkt('Asystentka/Asystent Zarządu – sprawnie połączysz priorytety i style pracy kilku członków zarządu.'),
      punkt('Executive Assistant – zbudujesz wpływ i sprawczość.'),
      h2('Dla kogo nie jest'),
      punkt('Dla osób na początku kariery. Pracuję w gronie doświadczonych Asystentek/Asystentów.'),
      punkt('Dla szukających kursu obsługi programów. Uczę myślenia i działania, narzędzia są wsparciem.'),
      punkt('Dla szukających strategii i finansów. To nie są tematy tego programu.'),

      // --- efekty ---
      nadtytul('Co się zmieni'),
      h1('Szkolenie, którego efekty zobaczy Zarząd'),
      ...[
        ['Mniej spraw na biurku szefa/szefowej', 'Uzgodniony zakres samodzielnych decyzji oznacza, że przejmujesz i zamykasz część spraw samodzielnie.'],
        ['Rekomendacje zamiast pytań', 'Przychodzisz do szefa/szefowej z propozycją rozwiązań. Szef/szefowa podejmuje decyzje szybciej, bez obciążenia poznawczego.'],
        ['Skuteczniejsza komunikacja zarządu z otoczeniem', 'Przygotowujesz informacje w imieniu zarządu, które doprowadzają do konkretnego działania.'],
        ['Jasne zasady w sprawach pilnych', 'Szef/szefowa wie, kiedy i jak trafią do niego/niej sprawy, które naprawdę wymagają tylko jego/jej uwagi czy decyzji.'],
        ['Bezpieczne AI w biurze zarządu', 'Korzystasz z AI świadomie i bezpiecznie.'],
        ['Efekt, który widać od razu po Twoim powrocie do pracy', 'Wychodzisz z własną mapą celów i planem wdrożenia na 90 dni.'],
      ].map(([tyt, op], i) => new Paragraph({
        spacing: { after: 130, line: 276 },
        indent: { left: 360, hanging: 360 },
        children: [
          t(String(i + 1).padStart(2, '0') + '  ', { size: 21, bold: true, color: GRANAT_JASNY }),
          t(tyt + '. ', { size: 21, bold: true, color: GRANAT }),
          t(op, { size: 21 }),
        ],
      })),

      // --- program ---
      new Paragraph({ pageBreakBefore: true, spacing: { after: 0 }, children: [] }),
      nadtytul('Program szkolenia'),
      h1('Dwa dni. Sześć modułów. Dwanaście narzędzi.'),
      p('Każdy moduł kończy się gotowym narzędziem, które zabierasz do biura – i wskazaniem, gdzie AI przyspiesza pracę, a gdzie decyduje Twój osąd.', { after: 240 }),
      dzien('Dzień 1', 'Myśl jak Szef'),
      ...moduly.flatMap(modul),
      pusty(280),
      dzien('Dzień 2', 'Działaj jak Partner'),
      ...moduly2.flatMap(modul),

      // --- bonusy ---
      nadtytul('Bonusy'),
      h1('Co dostajesz poza dwoma dniami warsztatów'),
      punkt('Zestaw 12 narzędzi – wszystkie szablony ze szkolenia w wersji do edycji.'),
      punkt('Biblioteka promptów AI dla Asystentek Zarządu – gotowe polecenia z zasadami bezpiecznego korzystania z AI.'),
      punkt('Dwie sesje grupowe online po szkoleniu – po 30 i 60 dniach sprawdzam postępy wdrożenia.'),

      // --- dlaczego warto ---
      nadtytul('Dlaczego warto'),
      h1('Pięć powodów, dla których ten program działa'),
      ...[
        ['Program odpowiada na to, czego dziś oczekują zarządy', 'Samodzielność, krytyczne myślenie, wpływ i sprawna komunikacja.'],
        ['Kompetencje miękkie i AI w jednym programie', 'W każdym module sprawdzasz, jak AI przyspiesza pracę i gdzie decyduje ludzki osąd.'],
        ['12 gotowych narzędzi', 'Mapa moich decyzji, mapa celów, szablon rekomendacji i inne. Do użycia od razu.'],
        ['Praktyka zamiast wykładu', 'Trzy czwarte czasu to symulacje, ćwiczenia z AI i praca na Twoich sytuacjach.'],
        ['Wsparcie we wdrożeniu', 'Dwie sesje online po 30 i 60 dniach w grupie absolwentek_ów szkolenia.'],
      ].map(([tyt, op], i) => new Paragraph({
        spacing: { after: 130, line: 276 },
        indent: { left: 360, hanging: 360 },
        children: [
          t(String(i + 1).padStart(2, '0') + '  ', { size: 21, bold: true, color: GRANAT_JASNY }),
          t(tyt + '. ', { size: 21, bold: true, color: GRANAT }),
          t(op, { size: 21 }),
        ],
      })),

      // --- cena ---
      new Paragraph({ pageBreakBefore: true, spacing: { after: 0 }, children: [] }),
      nadtytul('Warunki udziału'),
      h1('Cena'),
      p('2 100 zł netto za osobę (2 583 zł brutto, z 23% VAT). Od 3. osoby z tej samej firmy – 5% rabatu.', { after: 200 }),
      cennik,
      pusty(200),
      h2('Cena obejmuje'),
      punkt('2 dni warsztatów (I dzień 10.00–17.00, II dzień 9.00–16.00)'),
      punkt('12 gotowych narzędzi w wersji do edycji'),
      punkt('bibliotekę promptów AI dla Asystentek Zarządu'),
      punkt('2 sesje online po szkoleniu (2 × 45 minut, po 30 i 60 dniach)'),
      punkt('certyfikat ukończenia szkolenia'),
      punkt('przerwy kawowe i lunch'),

      h2('Zgłoszenie i płatność'),
      p('Zgłoszenie przyjmuję przez formularz na stronie szkolenia lub mailem na office@officeinfluencers.pl. W ciągu jednego dnia roboczego odsyłam potwierdzenie miejsca i fakturę pro forma.'),
      p('Zgłoszenie nie jest zobowiązaniem do zapłaty do czasu potwierdzenia z mojej strony. Fakturę VAT wystawiam na firmę lub osobę prywatną.'),

      // --- organizacja ---
      nadtytul('Organizacja'),
      h1('Termin i miejsce'),
      miejsce,
      pusty(160),
      p('Centrum Biznesowe Ogrodowa 58 leży w centrum Warszawy. Informacje o dojeździe i parkingu znajdują się na stronie budynku: ogrodowa58.com.pl/lokalizacja/', { size: 20, color: SZARY }),

      // --- prowadząca ---
      nadtytul('Prowadząca'),
      h1('Agnieszka Korach'),
      p('Trenerka biznesu i psycholożka (Uniwersytet SWPS), certyfikowana AI Educator. Od 23 lat rozwija kompetencje Asystentek Zarządu, Office Managerek i zespołów recepcji w Polsce.'),
      punkt('Doświadczenie: 23 lata pracy z zespołami asystenckimi – projekty otwarte i zamknięte.'),
      punkt('Przeszkolone osoby: ponad 10 000 Asystentek, Office Managerek i Recepcjonistek.'),
      punkt('Projekty: Mercedes-Benz, Bosch, 3M, Geberit, ERGO Hestia, Maspex, Solid Security, ABB.'),
      punkt('Społeczność: twórczyni Office Influencers – społeczności i marki szkoleniowej dla profesjonalistek biura.'),
      punkt('Program „Asystentka – Partner w Zarządzaniu” prowadzi nieprzerwanie od 2008 roku.'),

      // --- in-house ---
      nadtytul('Szkolenia zamknięte'),
      h1('Szkolenie dla zespołu w Twojej firmie'),
      p('Szkolenia dedykowane realizuję w miejscu wskazanym przez Klienta lub online. Program dostosowuję do specyfiki branży i kultury organizacji.'),
      p('Zaufały mi zespoły m.in.: Mercedes-Benz, ABB, 3M Poland, Grupa Maspex, Bosch.'),
      p('Wycenę przygotowuję indywidualnie – napisz lub zadzwoń.', { after: 260 }),

      // --- kontakt ---
      kreska(),
      new Paragraph({
        spacing: { after: 80 },
        children: [t('Kontakt', { size: 24, bold: true, color: GRANAT })],
      }),
      p('ARK Consulting Agnieszka Korach · Office Influencers'),
      p('E-mail: office@officeinfluencers.pl'),
      p('Telefon: +48 22 435 70 02 · +48 504 243 881'),
      p('Strona szkolenia: www.officeinfluencers.pl/asystentka-partner-w-zarzadzaniu-i-mysl-jak-szef/'),
    ],
  }],
});

Packer.toBuffer(doc).then((buf) => {
  fs.writeFileSync('/home/user/strona-ai/oferta-asystentka-partner-w-zarzadzaniu.docx', buf);
  console.log('zapisano, rozmiar:', buf.length, 'bajtów');
});
