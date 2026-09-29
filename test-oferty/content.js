/*
  WSZYSTKIE TEKSTY TESTU „Gdzie Twoja oferta traci klientów?”

  Jak edytować:
  - Zmieniaj tylko tekst między prostymi cudzysłowami "…".
  - W środku tekstu używaj polskich cudzysłowów „…” (nie prostych "…").
  - Nie usuwaj przecinków na końcu linii ani nawiasów { } [ ].
  - *gwiazdki* w nagłówku = żółte zaznaczenie markerem.
  - {numer}, {liczba}, {wynik}, {max}, {procent} aplikacja podmienia sama. Nie tłumacz ich.
  - Po zmianie punktów otwórz testy.html i sprawdź, czy wszystko jest na zielono.
*/
window.TRESC = {
  /* ---------- Ekran startowy ---------- */
  start: {
    tytulStrony: "Gdzie Twoja oferta traci klientów? Test Agnieszki Korach",
    naglowek: "Gdzie Twoja oferta *traci klientów?*",
    podtytul:
      "10 pytań, 5 minut. Sprawdzisz swoją ofertę przez 7 mechanizmów psychologii decyzji i dostaniesz gotowe polecenia dla AI, które pomogą Ci ją poprawić.",
    autorka:
      "Test przygotowała Agnieszka Korach, psycholożka społeczna. Od 25 lat uczy, jak ludzie podejmują decyzje.",
    przycisk: "Zaczynam test"
  },

  /* ---------- Pytania ---------- */
  pytania: {
    postep: "Pytanie {numer} z {liczba}",
    wstecz: "Wstecz",
    wczesniejszaOdpowiedz: "(Twoja wcześniejsza odpowiedź)",

    /* Kolejność odpowiedzi na ekranie = kolejność tutaj.
       mechanizm: id z listy „mechanizmy” niżej. */
    lista: [
      {
        mechanizm: "pierwsze-wrazenie",
        tresc: "Ktoś pierwszy raz wchodzi na Twój profil albo stronę. Czy w 5 sekund wie, komu pomagasz i z jakim efektem?",
        odpowiedzi: [
          { tekst: "Nie, najpierw widzi moje imię, logo i ogólne hasło", punkty: 0 },
          { tekst: "Częściowo: jest opis usług, ale bez efektu dla klienta", punkty: 1 },
          { tekst: "Tak: pierwsze zdanie mówi, dla kogo jestem i jaki daję efekt", punkty: 2 }
        ]
      },
      {
        mechanizm: "pierwsze-wrazenie",
        tresc: "Co klient widzi jako pierwsze w Twojej ofercie?",
        odpowiedzi: [
          { tekst: "Listę tego, co robię: usługi, metody, certyfikaty", punkty: 0 },
          { tekst: "Cenę i zakres", punkty: 1 },
          { tekst: "Swój problem i efekt, który dostanie", punkty: 2 }
        ]
      },
      {
        mechanizm: "efekt-kontrastu",
        tresc: "Ile wariantów oferty pokazujesz?",
        odpowiedzi: [
          { tekst: "Jeden: bierzesz albo nie", punkty: 0 },
          { tekst: "Kilka, zaczynając od najtańszego", punkty: 1 },
          { tekst: "2–3, a pierwszy widoczny jest najpełniejszy", punkty: 2 }
        ]
      },
      {
        mechanizm: "efekt-kontrastu",
        tresc: "Klient mówi „za drogo”. Co robisz najczęściej?",
        odpowiedzi: [
          { tekst: "Daję rabat", punkty: 0 },
          { tekst: "Tłumaczę, z czego wynika cena", punkty: 1 },
          { tekst: "Pokazuję cenę na tle kosztu problemu albo droższej alternatywy", punkty: 2 }
        ]
      },
      {
        mechanizm: "spoleczny-dowod",
        tresc: "Jak pokazujesz opinie klientów?",
        odpowiedzi: [
          { tekst: "Nie pokazuję albo mam tylko ogólne „polecam!”", punkty: 0 },
          { tekst: "Mam opinie, ale bez szczegółów", punkty: 1 },
          { tekst: "Pokazuję konkretne historie: kto, z jakim problemem, jaki efekt", punkty: 2 }
        ]
      },
      {
        mechanizm: "spoleczny-dowod",
        tresc: "Czy Twoje opinie pochodzą od osób podobnych do Twoich idealnych klientów?",
        odpowiedzi: [
          { tekst: "Nie wiem, nie zwracam na to uwagi", punkty: 0 },
          { tekst: "Częściowo", punkty: 1 },
          { tekst: "Tak, klient widzi w nich „kogoś takiego jak ja”", punkty: 2 }
        ]
      },
      {
        mechanizm: "wzajemnosc",
        tresc: "Co dajesz za darmo?",
        odpowiedzi: [
          { tekst: "Nic albo prawie nic", punkty: 0 },
          { tekst: "Dużo ogólnych porad dla wszystkich", punkty: 1 },
          { tekst: "Jedną konkretną rzecz dopasowaną do problemu klienta, która daje szybki efekt", punkty: 2 }
        ]
      },
      {
        mechanizm: "autorytet",
        tresc: "Skąd klient wie, że możesz mu pomóc?",
        odpowiedzi: [
          { tekst: "Z mojego wizerunku i stylu życia w social mediach", punkty: 0 },
          { tekst: "Z listy certyfikatów i szkoleń", punkty: 1 },
          { tekst: "Z konkretnych wyników klientów i mojego doświadczenia w jego problemie", punkty: 2 }
        ]
      },
      {
        mechanizm: "niedostepnosc",
        tresc: "Jak zachęcasz do decyzji teraz, a nie „kiedyś”?",
        odpowiedzi: [
          { tekst: "Promocjami z odliczaniem, które potem przedłużam", punkty: 0 },
          { tekst: "Nie mam terminów, klient decyduje, kiedy chce", punkty: 1 },
          { tekst: "Podaję prawdziwy termin albo limit, który wynika z mojej pracy", punkty: 2 }
        ]
      },
      {
        mechanizm: "zaangazowanie",
        tresc: "Jaki pierwszy krok proponujesz nowej osobie?",
        odpowiedzi: [
          { tekst: "Od razu główną, najdroższą usługę", punkty: 0 },
          { tekst: "Bezpłatną konsultację „na wszelki wypadek”", punkty: 1 },
          { tekst: "Mały, konkretny krok z jasnym efektem, np. płatny audyt albo krótką sesję", punkty: 2 }
        ]
      }
    ]
  },

  /* ---------- Wynik ogólny + formularz e-mail ---------- */
  wynikOgolny: {
    etykieta: "Twój wynik",
    punktyCzytnik: "{wynik} na {max} punktów",
    przedzialy: [
      { od: 0, do: 8, opis: "Twoja oferta gubi klientów w kilku miejscach naraz. Zacznij od trzech punktów poniżej." },
      { od: 9, do: 14, opis: "Masz solidne podstawy, ale klienci uciekają w konkretnych miejscach." },
      { od: 15, do: 20, opis: "Twoja oferta dobrze korzysta z psychologii decyzji. Zostały szczegóły." }
    ]
  },

  formularz: {
    wstep:
      "Podaj e-mail, a od razu zobaczysz 3 miejsca, w których Twoja oferta traci klientów, razem z gotowymi poleceniami dla AI.",
    wymagane: "Oba pola są wymagane.",
    etykietaEmail: "Adres e-mail",
    zgoda:
      "Wyrażam zgodę na otrzymywanie od ARK Consulting Agnieszka Korach newslettera na podany adres e-mail, w tym informacji handlowych o kursach i usługach (np. o mini-kursie). Zgodę mogę wycofać w każdej chwili.",
    linkPolityki: {
      tekst: "Polityka prywatności",
      adres: "https://agnieszkakorach.com/polityka-prywatnosci/"
    },
    klauzula:
      "Administratorem Twoich danych osobowych jest ARK Consulting Agnieszka Korach, kontakt: hello@agnieszkakorach.com. Adres e-mail przetwarzam na podstawie Twojej zgody (art. 6 ust. 1 lit. a RODO), żeby wysyłać Ci newsletter, do czasu wycofania zgody. Odbiorcą danych jest MailerLite, dostawca systemu do wysyłki newslettera. Masz prawo dostępu do swoich danych, ich sprostowania, usunięcia, ograniczenia przetwarzania i przenoszenia oraz prawo wniesienia skargi do Prezesa UODO. Wycofanie zgody nie wpływa na zgodność z prawem przetwarzania przed jej wycofaniem. Podanie adresu jest dobrowolne, ale bez niego nie mogę wysłać Ci newslettera ani pokazać szczegółowych wyników.",
    /* Pokazywane tylko wtedy, gdy w sekcji „newsletter” uzupełnisz poleWynik lub poleSlabeObszary. */
    klauzulaWyniki:
      "Razem z adresem zapisuję Twój wynik ogólny i nazwy 3 najsłabszych obszarów, żeby lepiej dopasować treści newslettera.",
    przycisk: "Pokaż moje wyniki",
    wysylanie: "Wysyłam…",
    bladEmailPusty: "Wpisz adres e-mail.",
    bladEmailFormat: "Ten adres wygląda na niepełny. Sprawdź, czy ma @ i domenę, np. imie@firma.pl.",
    bladZgoda: "Zaznacz zgodę, żeby zobaczyć wyniki.",
    bladWysylki: "Nie udało się wysłać formularza. Sprawdź połączenie z internetem i spróbuj jeszcze raz."
  },

  /* ---------- Wynik szczegółowy ---------- */
  wynikSzczegolowy: {
    naglowek: "3 miejsca, w których Twoja oferta *traci klientów*",
    etykietaWynik: "Wynik",
    procent: "{procent}%",
    etykietaMit: "Mit influencerów",
    etykietaBadania: "Co mówią badania",
    etykietaCoZrobic: "Co zrobić",
    etykietaPolecenie: "Polecenie dla AI",
    kopiuj: "Kopiuj polecenie",
    skopiowano: "Skopiowano",
    bladKopiowania: "Nie udało się skopiować. Zaznacz tekst polecenia i skopiuj go ręcznie.",
    trybTestowy: "Tryb testowy: e-mail nie został wysłany."
  },

  /* ---------- 7 mechanizmów ----------
     Kolejność na liście decyduje przy remisie (pierwszy wygrywa).
     id łączy mechanizm z pytaniami. Nie zmieniaj id. */
  mechanizmy: [
    {
      id: "pierwsze-wrazenie",
      nazwa: "Pierwsze wrażenie",
      mit: "„Dobry produkt obroni się sam.”",
      badania: "[DO UZUPEŁNIENIA: Agnieszka, 1–2 zdania o badaniu + źródło]",
      coZrobic:
        "Przepisz pierwsze zdanie profilu i oferty tak, żeby mówiło, komu pomagasz i z jakim efektem. Swoje imię i metody przesuń niżej.",
      prompt:
        "Jesteś ekspertem od psychologii pierwszego wrażenia w marketingu. Oto pierwsze zdania mojego profilu lub strony: [WKLEJ]. Mój idealny klient to: [OPISZ]. Oceń, czy ten klient w 5 sekund zrozumie: 1) dla kogo jestem, 2) jaki efekt daję, 3) co ma zrobić dalej. Wskaż, co go zatrzymuje. Zaproponuj 3 nowe wersje pierwszego zdania. Każda ma mówić, dla kogo jestem i z jakim efektem, maksymalnie 15 słów."
    },
    {
      id: "efekt-kontrastu",
      nazwa: "Efekt kontrastu",
      mit: "„Daj rabat, to kupią.”",
      badania: "[DO UZUPEŁNIENIA: Agnieszka, 1–2 zdania o badaniu + źródło]",
      coZrobic:
        "Pokaż 2–3 pakiety, zaczynając od najpełniejszego. Na »za drogo« odpowiadaj porównaniem z kosztem problemu, nie rabatem.",
      prompt:
        "Oto moja oferta i ceny: [WKLEJ]. Zaprojektuj 3 pakiety z użyciem efektu kontrastu: najpełniejszy pokazywany jako pierwszy, główny (ten, który chcę sprzedawać najczęściej) i podstawowy. Dla każdego podaj nazwę, zawartość i cenę. Wyjaśnij, dlaczego taka kolejność ułatwia klientowi decyzję. Napisz też 3 odpowiedzi na „za drogo”, które pokazują cenę na tle kosztu problemu. Nie proponuj rabatów."
    },
    {
      id: "spoleczny-dowod",
      nazwa: "Społeczny dowód słuszności",
      mit: "„Liczy się liczba obserwujących.”",
      badania: "[DO UZUPEŁNIENIA: Agnieszka, 1–2 zdania o badaniu + źródło]",
      coZrobic:
        "Zamień ogólne opinie na krótkie historie klientów podobnych do tych, których chcesz przyciągnąć.",
      prompt:
        "Oto opinie moich klientów: [WKLEJ]. Mój idealny klient to: [OPISZ]. Wybierz 3 opinie najbardziej podobne do sytuacji idealnego klienta. Z każdej napisz krótką historię w schemacie: kim był klient, z czym przyszedł, co się zmieniło. Nie dodawaj niczego, czego nie ma w opinii. Na koniec wypisz, jakich informacji brakuje i o co warto dopytać klientów."
    },
    {
      id: "wzajemnosc",
      nazwa: "Wzajemność",
      mit: "„Dawaj wszystko za darmo.”",
      badania: "[DO UZUPEŁNIENIA: Agnieszka, 1–2 zdania o badaniu + źródło]",
      coZrobic:
        "Zamiast wielu ogólnych porad daj jedną konkretną rzecz, która w 15 minut rozwiązuje mały problem Twojego klienta.",
      prompt:
        "Mój idealny klient to: [OPISZ]. Jego główny problem to: [OPISZ]. Moja płatna usługa to: [OPISZ]. Zaproponuj 5 pomysłów na darmowy materiał, który daje konkretny efekt w mniej niż 15 minut, dotyczy tego jednego problemu i naturalnie prowadzi do mojej płatnej usługi. Oceń każdy pomysł w skali 1–5 pod kątem wartości dla klienta i czasu przygotowania."
    },
    {
      id: "autorytet",
      nazwa: "Autorytet",
      mit: "„Pokazuj luksus, to Ci zaufają.”",
      badania: "[DO UZUPEŁNIENIA: Agnieszka, 1–2 zdania o badaniu + źródło]",
      coZrobic:
        "Pokaż wyniki klientów i liczby z Twojej praktyki zamiast listy certyfikatów.",
      prompt:
        "Oto informacje o mnie: doświadczenie, wyniki klientów, liczby: [WKLEJ]. Mój idealny klient to: [OPISZ]. Wybierz 3 dowody kompetencji, które najmocniej przekonają tego klienta. Napisz z nich krótkie „O mnie” w 3 zdaniach: konkret i liczby, bez przymiotników typu „profesjonalny” czy „z pasją”. Nie dodawaj niczego, czego nie podałam."
    },
    {
      id: "niedostepnosc",
      nazwa: "Niedostępność",
      mit: "„Odliczaj i twórz presję.”",
      badania: "[DO UZUPEŁNIENIA: Agnieszka, 1–2 zdania o badaniu + źródło]",
      coZrobic:
        "Używaj tylko prawdziwych terminów i limitów. Fałszywe odliczanie działa raz, a zaufanie tracisz na stałe.",
      prompt:
        "Moja usługa to: [OPISZ]. Pracuję tak: [np. ilu klientów miesięcznie mogę przyjąć, kiedy startują grupy]. Pomóż mi znaleźć prawdziwe powody, dla których warto zdecydować teraz: limity, terminy, koszt czekania. Odrzuć wszystko, co byłoby fałszywą presją. Napisz 3 krótkie komunikaty, które uczciwie informują o terminie lub limicie."
    },
    {
      id: "zaangazowanie",
      nazwa: "Zaangażowanie i konsekwencja",
      mit: "„Od razu sprzedawaj duży produkt.”",
      badania: "[DO UZUPEŁNIENIA: Agnieszka, 1–2 zdania o badaniu + źródło]",
      coZrobic:
        "Zaproponuj nowym osobom mały pierwszy krok z konkretnym efektem, zanim zaproponujesz główną usługę.",
      prompt:
        "Moja główna usługa to: [OPISZ], cena: [CENA]. Klienci często wahają się przed pierwszym zakupem. Zaprojektuj ścieżkę 3 małych kroków prowadzących do głównej usługi. Każdy krok ma być mały pod względem czasu, pieniędzy i ryzyka, dawać konkretny efekt i naturalnie prowadzić do następnego. Podaj nazwę, format i orientacyjną cenę każdego kroku."
    }
  ],

  /* ---------- CTA na końcu ---------- */
  cta: {
    naglowek: "Chcesz przejść wszystkie 7 mechanizmów?",
    tekst: "[DO UZUPEŁNIENIA: 1–2 zdania o mini-kursie + termin startu]",
    przycisk: "[DO UZUPEŁNIENIA: etykieta]",
    link: "[DO UZUPEŁNIENIA: link]"
  },

  /* ---------- Teksty wspólne ---------- */
  wspolne: {
    nowaKarta: "(otwiera się w nowej karcie)"
  },

  /* ---------- Formularz MailerLite ----------
     Wszystko przepisujesz z kodu osadzanego formularza MailerLite (instrukcja w README.md).
     Puste adresFormularza = TRYB TESTOWY: wyniki pokazują się bez wysyłki e-maila. */
  newsletter: {
    adresFormularza: "",
    poleEmail: "fields[email]",
    polaStale: {
      "ml-submit": "1",
      "anticsrf": "true"
    },
    /* Opcjonalnie: nazwy pól własnych z MailerLite, np. "fields[wynik]".
       Puste = te dane nie są wysyłane. */
    poleWynik: "",
    poleSlabeObszary: ""
  }
};
