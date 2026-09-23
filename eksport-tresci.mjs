import { chromium } from 'playwright';
import fs from 'node:fs';

const WYJSCIE = '/home/user/strona-ai/TRESC-STRONY.md';

const b = await chromium.launch({ executablePath: '/opt/pw-browsers/chromium-1194/chrome-linux/chrome' });
const p = await b.newPage();
await p.goto('http://127.0.0.1:8871/asystentka-partner-w-zarzadzaniu-i-mysl-jak-szef/', { waitUntil: 'domcontentloaded' });
await p.waitForTimeout(600);

await p.evaluate(() => {
  for (const el of document.querySelectorAll('.form-done')) el.removeAttribute('hidden');
});

const dane = await p.evaluate(() => {
  const POMIJAJ = new Set(['SCRIPT', 'STYLE', 'NOSCRIPT', 'SVG', 'TEMPLATE']);

  // zamiana treści blokowej na markdown, z zachowaniem odnośników i wyróżnień
  function inline(el) {
    let out = '';
    for (const w of el.childNodes) {
      if (w.nodeType === 3) { out += w.textContent; continue; }
      if (w.nodeType !== 1 || POMIJAJ.has(w.tagName)) continue;
      if (w.hasAttribute('aria-hidden') && w.getAttribute('aria-hidden') === 'true') continue;
      const t = w.tagName;
      if (t === 'BR') { out += '\n'; continue; }
      const srodek = inline(w);
      // <small> i <i> stoją w kodzie bez spacji przed nimi, a na ekranie są osobno
      if ((t === 'SMALL' || t === 'I') && out && !/\s$/.test(out)) out += ' ';
      if (t === 'A') {
        const href = w.getAttribute('href') || '';
        out += href && !href.startsWith('#') ? `[${srodek}](${href})` : srodek;
      } else if (t === 'B' || t === 'STRONG') {
        out += `**${srodek}**`;
      } else if (t === 'EM' || t === 'I' || t === 'MARK') {
        out += srodek;
      } else {
        out += srodek;
      }
    }
    return out;
  }

  const czysc = s => s.replace(/[ \t]+/g, ' ').replace(/ ?\n ?/g, '\n').trim();

  const wiersze = [];
  const widziane = new Set();
  let ostatniaSekcja = null;

  function sekcjaDla(el) {
    const s = el.closest('section, header, footer, dialog, .topbar, .cookies');
    return s ? (s.id || s.className.split(' ')[0] || s.tagName.toLowerCase()) : 'strona';
  }

  const SELEKTOR = 'h1, h2, h3, h4, p, li, dt, dd, blockquote > p, figcaption, summary, '
    + 'label, legend, option, .liczba, .liczba-opis, .etykieta, .kicker, .badge, '
    + 'button, .btn, a.btn, th, td, '
    + '.site-footer .bar > span, .topbar__label, .topbar__cta, .cookies__txt, '
    + '.amount, .cena, .price-card .note, .summary dd';

  for (const el of document.querySelectorAll(SELEKTOR)) {
    if (el.closest('.hp')) continue;                 // pole pułapka
    if (el.closest('script, style, noscript')) continue;
    if (el.offsetParent === null && !el.closest('dialog')) continue;   // ukryte

    const sek = sekcjaDla(el);
    if (sek !== ostatniaSekcja) {
      wiersze.push({ typ: 'sekcja', tekst: sek });
      ostatniaSekcja = sek;
    }

    const tekst = czysc(inline(el));
    if (!tekst) continue;

    const klucz = sek + '|' + el.tagName + '|' + tekst;
    if (widziane.has(klucz)) continue;
    widziane.add(klucz);

    wiersze.push({ typ: el.tagName.toLowerCase(), tekst, klasa: el.className || '' });
  }

  return {
    tytul: document.title,
    opis: (document.querySelector('meta[name=description]') || {}).content || '',
    ogTytul: (document.querySelector('meta[property="og:title"]') || {}).content || '',
    ogOpis: (document.querySelector('meta[property="og:description"]') || {}).content || '',
    wiersze,
  };
});

await b.close();

const NAZWY = {
  'topbar': 'Pasek u góry z terminem i licznikiem',
  'site-header': 'Belka nawigacji',
  'hero': 'Nagłówek strony',
  'section': 'Blok przed formularzem',
  'site-footer': 'Stopka',
  'cookies': 'Baner zgody na statystyki',
  'klienci': 'Zaufali mi',
  'sytuacje': 'Cztery sytuacje',
  'efekty': 'Co się zmieni w pracy',
  'narzedzie': 'Kalkulator',
  'po-powrocie': 'Po powrocie do pracy',
  'dla-kogo': 'Dla kogo',
  'program': 'Program',
  'ai': 'Rola AI',
  'wsparcie': 'Wsparcie po szkoleniu',
  'prowadzaca': 'O mnie',
  'cena': 'Cena',
  'uzasadnienie': 'Uzasadnienie dla przełożonego',
  'zapis': 'Formularz zgłoszenia',
  'faq': 'Pytania i odpowiedzi',
  'in-house': 'Szkolenia zamknięte',
  'oferta': 'Okno zapytania ofertowego',
};

const poziom = { h1: '#', h2: '##', h3: '###', h4: '####' };
const out = [];

out.push('# Treść strony: Asystentka – Partner w Zarządzaniu I. Myśl jak Szef');
out.push('');
out.push('Popraw tekst i odeślij mi ten plik. Przeniosę zmiany na stronę.');
out.push('');
out.push('**Jak edytować, żeby nic się nie rozjechało:**');
out.push('');
out.push('- Zmieniaj **tylko tekst**. Nie ruszaj wierszy zaczynających się od `<!--` ani nagłówków `## SEKCJA:`, po nich trafiam z powrotem w odpowiednie miejsce.');
out.push('- Chcesz coś usunąć: skreśl treść, ale **zostaw sam wiersz pusty w tym miejscu** albo dopisz obok `[USUŃ]`. Nie kasuj całych bloków, bo stracę punkt zaczepienia.');
out.push('- Chcesz coś dopisać: wpisz nowy akapit tam, gdzie ma się znaleźć, i dopisz obok `[NOWE]`.');
out.push('- Nie używaj długich myślników. Półpauza `–` tylko w zakresach, na przykład 19–20 listopada.');
out.push('- Odnośniki w nawiasach, na przykład `[polityką prywatności](https://...)`, zostaw jak są, chyba że chcesz zmienić sam adres.');
out.push('');
out.push('W tym pliku jest wyłącznie treść widoczna na stronie. Wszystko, co działa pod spodem, czyli formularze, kalkulator, licznik i zabezpieczenia, zostaje bez zmian.');
out.push('');
out.push('---');
out.push('');
out.push('## SEKCJA: meta');
out.push('');
out.push('<!-- tytuł w zakładce przeglądarki i w wynikach Google, do 60 znaków -->');
out.push('');
out.push('TYTUŁ: ' + dane.tytul);
out.push('');
out.push('<!-- opis pod tytułem w wynikach Google, do 160 znaków -->');
out.push('');
out.push('OPIS: ' + dane.opis);
out.push('');
out.push('<!-- tytuł i opis przy udostępnianiu linku w mediach społecznościowych -->');
out.push('');
out.push('TYTUŁ UDOSTĘPNIANIA: ' + dane.ogTytul);
out.push('');
out.push('OPIS UDOSTĘPNIANIA: ' + dane.ogOpis);
out.push('');

let wSekcji = 0;
for (const w of dane.wiersze) {
  if (w.typ === 'sekcja') {
    out.push('');
    out.push('---');
    out.push('');
    const nazwa = NAZWY[w.tekst] || w.tekst;
    out.push(`## SEKCJA: ${w.tekst}` + (NAZWY[w.tekst] ? `  (${nazwa})` : ''));
    out.push('');
    wSekcji = 0;
    continue;
  }
  wSekcji++;
  const t = w.tekst.replace(/\n/g, '  \n');
  if (poziom[w.typ]) {
    out.push('');
    out.push(`${poziom[w.typ]}# ${t}`);
    out.push('');
  } else if (w.typ === 'li') {
    out.push(`- ${t}`);
  } else if (w.typ === 'label' || w.typ === 'legend') {
    out.push(`POLE: ${t}`);
  } else if (w.typ === 'option') {
    out.push(`  opcja: ${t}`);
  } else if (w.typ === 'button' || (w.klasa || '').includes('btn')) {
    out.push(`PRZYCISK: ${t}`);
  } else if (w.typ === 'dt') {
    out.push(`- ${t}:`);
  } else if (w.typ === 'dd') {
    out.push(`  ${t}`);
  } else if (w.typ === 'th' || w.typ === 'td') {
    out.push(`| ${t}`);
  } else {
    out.push('');
    out.push(t);
    out.push('');
  }
}

const zrodlo = fs.readFileSync('/home/user/strona-ai/asystentka-landing.html', 'utf8');
function zeZrodla(wzor) {
  const m = zrodlo.match(wzor);
  return m ? m[1] : null;
}
const KOMUNIKATY = [
  ['licznik, po zamknięciu zapisów', /etykieta\.textContent = '([^']+)'/],
  ['licznik, w dniach szkolenia', /new Date\(\) < DO_KONCA\s*\?\s*'([^']+)'/],
  ['licznik, po szkoleniu', /DO_KONCA\s*\?\s*'[^']+'\s*:\s*'([^']+)'/],
  ['formularz, nie udało się wysłać', /status\.textContent='([^']*Nie udało się wysłać zgłoszenia[^']*)'/],
  ['NIP, nie udało się pobrać', /var NIP_BLAD='([^']+)'/],
  ['NIP, dane pobrane', /nipHint\.textContent='(Dane z )'/],
];
out.push('');
out.push('---');
out.push('');
out.push('## SEKCJA: komunikaty  (Napisy pokazujące się w trakcie)');
out.push('');
out.push('<!-- te teksty siedzą w skrypcie strony, nie w treści. Też można je zmienić. -->');
out.push('');
for (const [opis, wzor] of KOMUNIKATY) {
  const v = zeZrodla(wzor);
  if (v) {
    const czysty = opis.startsWith('NIP, dane pobrane')
      ? 'Dane z [nazwa rejestru]. Sprawdź je i popraw, jeśli trzeba.'
      : v.replace(/\\u2013/g, '\u2013');
    out.push(`- ${opis}: ${czysty}`);
  }
}
out.push('');
out.push('Komunikaty przy błędnie wypełnionych polach (imię bez nazwiska, zły NIP, zły telefon) '
  + 'zostawiam poza tym plikiem. Jest ich kilkanaście i są zestrojone z tym, co sprawdza serwer. '
  + 'Jeśli któryś Ci nie pasuje, napisz, poprawię go po obu stronach naraz.');
out.push('');

let tekst = out.join('\n').replace(/\n{3,}/g, '\n\n') + '\n';
fs.writeFileSync(WYJSCIE, tekst);

const znaki = tekst.length;
const sekcji = (tekst.match(/^## SEKCJA:/gm) || []).length;
console.log('zapisano:', WYJSCIE);
console.log('sekcji:', sekcji, '| wierszy treści:', dane.wiersze.filter(w => w.typ !== 'sekcja').length, '| znaków:', znaki);
