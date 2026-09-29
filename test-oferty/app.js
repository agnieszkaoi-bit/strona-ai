/*
  Logika testu „Gdzie Twoja oferta traci klientów?”.
  Teksty są w content.js. Ten plik nie zawiera treści do edycji.
  Nic nie jest zapisywane: bez ciasteczek, localStorage i skryptów śledzących.
*/
(function () {
  "use strict";

  /* =========================================================
     PUNKTACJA (czyste funkcje, bez DOM, sprawdzane w testy.html)
     ========================================================= */

  function suma(liczby) {
    return liczby.reduce(function (a, b) { return a + b; }, 0);
  }

  function maksPytania(pytanie) {
    return Math.max.apply(null, pytanie.odpowiedzi.map(function (o) { return o.punkty; }));
  }

  function maksimumTestu(tresc) {
    return suma(tresc.pytania.lista.map(maksPytania));
  }

  // wybory: indeksy wybranych odpowiedzi (0, 1, 2) dla kolejnych pytań
  function punktyZWyborow(tresc, wybory) {
    return tresc.pytania.lista.map(function (pytanie, i) {
      var odpowiedz = pytanie.odpowiedzi[wybory[i]];
      return odpowiedz ? odpowiedz.punkty : 0;
    });
  }

  // punkty: punkty za kolejne pytania. Wynik w kolejności listy mechanizmów.
  function wynikiMechanizmow(tresc, punkty) {
    return tresc.mechanizmy.map(function (mechanizm, indeks) {
      var zdobyte = 0;
      var maks = 0;
      tresc.pytania.lista.forEach(function (pytanie, i) {
        if (pytanie.mechanizm !== mechanizm.id) return;
        zdobyte += punkty[i] || 0;
        maks += maksPytania(pytanie);
      });
      return {
        mechanizm: mechanizm,
        indeks: indeks,
        punkty: zdobyte,
        maks: maks,
        procent: maks > 0 ? (zdobyte / maks) * 100 : 100
      };
    });
  }

  // Najniższy procent. Przy remisie wygrywa wcześniejszy mechanizm na liście.
  // Porównanie na ułamkach (bez zaokrągleń), więc 1/2 i 2/4 to remis.
  function najslabsze(wyniki, ile) {
    return wyniki
      .filter(function (w) { return w.maks > 0; })
      .sort(function (a, b) {
        return (a.punkty * b.maks - b.punkty * a.maks) || (a.indeks - b.indeks);
      })
      .slice(0, ile);
  }

  function przedzial(tresc, wynik) {
    return tresc.wynikOgolny.przedzialy.filter(function (p) {
      return wynik >= p.od && wynik <= p.do;
    })[0] || null;
  }

  window.TestOferty = {
    punktacja: {
      suma: suma,
      maksimumTestu: maksimumTestu,
      punktyZWyborow: punktyZWyborow,
      wynikiMechanizmow: wynikiMechanizmow,
      najslabsze: najslabsze,
      przedzial: przedzial
    }
  };

  /* =========================================================
     INTERFEJS
     ========================================================= */

  var T = window.TRESC;
  var root = document.getElementById("test-oferty");
  if (!T || !root) return;

  var stan = { pytanie: 0, wybory: [], wysylanie: false };
  var pierwszyEkran = true;

  function el(tag, atrybuty, dzieci) {
    var wezel = document.createElement(tag);
    Object.keys(atrybuty || {}).forEach(function (klucz) {
      var wartosc = atrybuty[klucz];
      if (wartosc === null || wartosc === undefined || wartosc === false) return;
      if (klucz === "class") wezel.className = wartosc;
      else if (klucz.slice(0, 2) === "on") wezel.addEventListener(klucz.slice(2), wartosc);
      else wezel.setAttribute(klucz, wartosc === true ? "" : wartosc);
    });
    [].concat(dzieci === undefined ? [] : dzieci).forEach(function (dziecko) {
      if (dziecko === null || dziecko === undefined || dziecko === false) return;
      wezel.appendChild(typeof dziecko === "string" ? document.createTextNode(dziecko) : dziecko);
    });
    return wezel;
  }

  function wstaw(szablon, dane) {
    return String(szablon).replace(/\{(\w+)\}/g, function (calosc, klucz) {
      return klucz in dane ? dane[klucz] : calosc;
    });
  }

  // *tekst* -> żółte zaznaczenie
  function zZaznaczeniem(tekst) {
    var fragment = document.createDocumentFragment();
    String(tekst).split("*").forEach(function (czesc, i) {
      if (!czesc) return;
      fragment.appendChild(i % 2 ? el("mark", { class: "to-marker" }, czesc) : document.createTextNode(czesc));
    });
    return fragment;
  }

  function linkNowaKarta(atrybuty, dzieci) {
    atrybuty.target = "_blank";
    atrybuty.rel = "noopener";
    return el("a", atrybuty, [].concat(dzieci, el("span", { class: "to-sr" }, " " + T.wspolne.nowaKarta)));
  }

  function trybTestowy() {
    return !(T.newsletter && T.newsletter.adresFormularza);
  }

  function wysylaWyniki() {
    return !!(T.newsletter && (T.newsletter.poleWynik || T.newsletter.poleSlabeObszary));
  }

  function pokaz(ekran) {
    while (root.firstChild) root.removeChild(root.firstChild);
    root.appendChild(ekran);
    if (pierwszyEkran) {
      pierwszyEkran = false;
      return;
    }
    if (root.getBoundingClientRect().top < 0) root.scrollIntoView();
    var naglowek = ekran.querySelector("h1");
    if (naglowek) naglowek.focus({ preventScroll: true });
  }

  /* ---------- Ekran 1: start ---------- */

  function pokazStart() {
    var S = T.start;
    pokaz(el("section", { class: "to-ekran to-start" }, el("div", { class: "to-kolumna" }, [
      el("h1", { class: "to-start-tytul", tabindex: "-1" }, zZaznaczeniem(S.naglowek)),
      el("div", { class: "to-start-dol" }, [
        el("p", { class: "to-start-podtytul" }, S.podtytul),
        el("p", { class: "to-start-autorka" }, S.autorka),
        el("button", {
          type: "button",
          class: "to-przycisk",
          onclick: function () {
            stan.pytanie = 0;
            pokazPytanie();
          }
        }, S.przycisk)
      ])
    ])));
  }

  /* ---------- Ekran 2: pytania ---------- */

  function pokazPytanie() {
    var P = T.pytania;
    var i = stan.pytanie;
    var pytanie = P.lista[i];
    var liczba = P.lista.length;

    var postep = el("div", { class: "to-postep", "aria-hidden": "true" }, P.lista.map(function (_, k) {
      return el("span", { class: k <= i ? "to-postep-krok to-postep-krok--zrobiony" : "to-postep-krok" });
    }));

    var odpowiedzi = el("ol", { class: "to-odpowiedzi", role: "list" }, pytanie.odpowiedzi.map(function (odpowiedz, k) {
      var wybrana = stan.wybory[i] === k;
      return el("li", null, el("button", {
        type: "button",
        class: wybrana ? "to-odpowiedz to-odpowiedz--wybrana" : "to-odpowiedz",
        onclick: function () { wybierz(i, k); }
      }, [
        el("span", null, odpowiedz.tekst),
        wybrana ? el("span", { class: "to-sr" }, " " + P.wczesniejszaOdpowiedz) : null
      ]));
    }));

    pokaz(el("section", { class: "to-ekran to-pytanie" }, el("div", { class: "to-kolumna" }, [
      postep,
      el("span", { class: "to-pytanie-cyfra", "aria-hidden": "true" }, (i < 9 ? "0" : "") + (i + 1)),
      el("h1", { class: "to-pytanie-tytul", tabindex: "-1" }, [
        el("span", { class: "to-pytanie-numer" }, wstaw(P.postep, { numer: i + 1, liczba: liczba })),
        el("span", null, pytanie.tresc)
      ]),
      odpowiedzi,
      el("button", {
        type: "button",
        class: "to-wstecz",
        onclick: function () {
          if (stan.pytanie === 0) {
            pokazStart();
          } else {
            stan.pytanie -= 1;
            pokazPytanie();
          }
        }
      }, P.wstecz)
    ])));
  }

  function wybierz(i, k) {
    stan.wybory[i] = k;
    if (i + 1 < T.pytania.lista.length) {
      stan.pytanie = i + 1;
      pokazPytanie();
    } else {
      pokazWynik();
    }
  }

  /* ---------- Ekran 3: wynik ogólny + formularz ---------- */

  function obliczenia() {
    var punkty = punktyZWyborow(T, stan.wybory);
    return {
      wynik: suma(punkty),
      maks: maksimumTestu(T),
      slabe: najslabsze(wynikiMechanizmow(T, punkty), 3)
    };
  }

  function pokazWynik() {
    var W = T.wynikOgolny;
    var F = T.formularz;
    var dane = obliczenia();
    var opis = przedzial(T, dane.wynik);

    var poleEmail = el("input", {
      id: "to-email",
      class: "to-pole-email",
      type: "email",
      name: "email",
      autocomplete: "email",
      inputmode: "email",
      spellcheck: "false",
      required: true,
      "aria-describedby": "to-email-blad"
    });
    var bladEmail = el("p", { id: "to-email-blad", class: "to-blad", hidden: true });

    var poleZgoda = el("input", {
      id: "to-zgoda",
      class: "to-zgoda-pole",
      type: "checkbox",
      name: "zgoda",
      required: true,
      "aria-describedby": "to-zgoda-blad"
    });
    var bladZgoda = el("p", { id: "to-zgoda-blad", class: "to-blad", hidden: true });

    var przycisk = el("button", { type: "submit", class: "to-przycisk" }, F.przycisk);
    var bladWysylki = el("p", { class: "to-blad", role: "alert" });

    function pokazBlad(pole, blad, tekst) {
      blad.textContent = tekst || "";
      blad.hidden = !tekst;
      if (tekst) pole.setAttribute("aria-invalid", "true");
      else pole.removeAttribute("aria-invalid");
    }

    function sprawdz() {
      var email = poleEmail.value.trim();
      var tekstEmail = !email ? F.bladEmailPusty
        : !/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(email) ? F.bladEmailFormat
        : "";
      var tekstZgoda = poleZgoda.checked ? "" : F.bladZgoda;
      pokazBlad(poleEmail, bladEmail, tekstEmail);
      pokazBlad(poleZgoda, bladZgoda, tekstZgoda);
      if (tekstEmail) poleEmail.focus();
      else if (tekstZgoda) poleZgoda.focus();
      return !tekstEmail && !tekstZgoda;
    }

    var formularz = el("form", {
      class: "to-formularz",
      novalidate: true,
      "aria-labelledby": "to-formularz-wstep",
      onsubmit: function (zdarzenie) {
        zdarzenie.preventDefault();
        if (stan.wysylanie) return;
        bladWysylki.textContent = "";
        if (!sprawdz()) return;

        if (trybTestowy()) {
          pokazSzczegoly(dane);
          return;
        }

        stan.wysylanie = true;
        przycisk.setAttribute("aria-disabled", "true");
        przycisk.textContent = F.wysylanie;
        wyslij(poleEmail.value.trim(), dane).then(function () {
          stan.wysylanie = false;
          pokazSzczegoly(dane);
        }, function () {
          stan.wysylanie = false;
          przycisk.removeAttribute("aria-disabled");
          przycisk.textContent = F.przycisk;
          bladWysylki.textContent = F.bladWysylki;
        });
      }
    }, [
      el("p", { id: "to-formularz-wstep", class: "to-formularz-wstep" }, F.wstep),
      el("p", { class: "to-wymagane" }, F.wymagane),
      el("div", { class: "to-pole" }, [
        el("label", { for: "to-email", class: "to-etykieta-pola" }, F.etykietaEmail),
        poleEmail,
        bladEmail
      ]),
      el("div", { class: "to-zgoda" }, [
        poleZgoda,
        el("label", { for: "to-zgoda", class: "to-zgoda-tekst" }, F.zgoda),
        el("p", { class: "to-polityka" }, linkNowaKarta({ href: F.linkPolityki.adres }, F.linkPolityki.tekst)),
        bladZgoda
      ]),
      przycisk,
      bladWysylki,
      el("div", { class: "to-klauzula" }, [
        el("p", null, F.klauzula),
        wysylaWyniki() ? el("p", null, F.klauzulaWyniki) : null
      ])
    ]);

    pokaz(el("section", { class: "to-ekran to-wynik" }, el("div", { class: "to-kolumna" }, [
      el("h1", { class: "to-wynik-tytul", tabindex: "-1" }, [
        el("span", { class: "to-etykieta" }, W.etykieta),
        el("span", { class: "to-wynik-liczba", "aria-hidden": "true" }, [
          String(dane.wynik),
          el("span", { class: "to-wynik-maks" }, "/" + dane.maks)
        ]),
        el("span", { class: "to-sr" }, " " + wstaw(W.punktyCzytnik, { wynik: dane.wynik, max: dane.maks }))
      ]),
      opis ? el("p", { class: "to-wynik-opis" }, opis.opis) : null,
      formularz
    ])));
  }

  // Wysyłka na adres oficjalnego formularza MailerLite (bez kluczy API).
  function wyslij(email, dane) {
    var N = T.newsletter;
    var pola = new URLSearchParams();
    pola.append(N.poleEmail, email);
    Object.keys(N.polaStale || {}).forEach(function (klucz) {
      pola.append(klucz, N.polaStale[klucz]);
    });
    if (N.poleWynik) pola.append(N.poleWynik, String(dane.wynik));
    if (N.poleSlabeObszary) {
      pola.append(N.poleSlabeObszary, dane.slabe.map(function (w) { return w.mechanizm.nazwa; }).join(", "));
    }
    return fetch(N.adresFormularza, { method: "POST", mode: "no-cors", body: pola });
  }

  /* ---------- Ekran 4: wynik szczegółowy ---------- */

  function pokazSzczegoly(dane) {
    var S = T.wynikSzczegolowy;
    var C = T.cta;

    pokaz(el("section", { class: "to-ekran to-szczegoly" }, [
      el("div", { class: "to-kolumna" }, [
        trybTestowy() ? el("p", { class: "to-tryb-testowy" }, S.trybTestowy) : null,
        el("h1", { class: "to-szczegoly-tytul", tabindex: "-1" }, zZaznaczeniem(S.naglowek)),
        el("ol", { class: "to-mechanizmy", role: "list" }, dane.slabe.map(blokMechanizmu))
      ]),
      el("section", { class: "to-cta", "aria-labelledby": "to-cta-tytul" }, el("div", { class: "to-kolumna" }, [
        el("h2", { id: "to-cta-tytul", class: "to-cta-tytul" }, zZaznaczeniem(C.naglowek)),
        el("p", { class: "to-cta-tekst" }, C.tekst),
        linkNowaKarta({ href: C.link, class: "to-przycisk to-przycisk--ciemny" }, C.przycisk)
      ]))
    ]));
  }

  function blokMechanizmu(wynik) {
    var S = T.wynikSzczegolowy;
    var m = wynik.mechanizm;
    var procent = Math.round(wynik.procent);
    var idNazwy = "to-mech-" + m.id;

    function wiersz(etykieta, tresc, klasa) {
      return el("div", { class: "to-opis-wiersz" }, [
        el("dt", null, etykieta),
        el("dd", { class: klasa || null }, tresc)
      ]);
    }

    return el("li", { class: "to-mechanizm" }, el("article", { "aria-labelledby": idNazwy }, [
      el("div", { class: "to-mechanizm-glowa" }, [
        el("h2", { id: idNazwy, class: "to-mechanizm-nazwa" }, m.nazwa),
        el("p", { class: "to-mechanizm-wynik" }, [
          el("span", { class: "to-etykieta" }, S.etykietaWynik + " "),
          el("span", { class: "to-mechanizm-procent" }, wstaw(S.procent, { procent: procent }))
        ])
      ]),
      el("div", { class: "to-miernik", "aria-hidden": "true" }, el("span", { style: "width:" + procent + "%" })),
      el("dl", { class: "to-opis" }, [
        wiersz(S.etykietaMit, m.mit, "to-mit"),
        wiersz(S.etykietaBadania, m.badania),
        wiersz(S.etykietaCoZrobic, m.coZrobic)
      ]),
      blokPolecenia(m)
    ]));
  }

  function blokPolecenia(m) {
    var S = T.wynikSzczegolowy;
    var idEtykiety = "to-pol-" + m.id;
    var komunikat = el("span", { class: "to-sr", "aria-live": "polite" });
    var blad = el("p", { class: "to-blad", "aria-live": "polite" });
    var licznik = null;

    var przycisk = el("button", {
      type: "button",
      class: "to-przycisk to-przycisk--kopiuj",
      onclick: function () {
        blad.textContent = "";
        kopiuj(m.prompt, przycisk).then(function () {
          przycisk.textContent = S.skopiowano;
          przycisk.classList.add("to-przycisk--skopiowano");
          komunikat.textContent = S.skopiowano;
          clearTimeout(licznik);
          licznik = setTimeout(function () {
            przycisk.textContent = S.kopiuj;
            przycisk.classList.remove("to-przycisk--skopiowano");
            komunikat.textContent = "";
          }, 2500);
        }, function () {
          blad.textContent = S.bladKopiowania;
        });
      }
    }, S.kopiuj);

    return el("div", { class: "to-polecenie", role: "group", "aria-labelledby": idEtykiety }, [
      el("p", { id: idEtykiety, class: "to-polecenie-etykieta" }, S.etykietaPolecenie),
      el("p", { class: "to-polecenie-tekst" }, m.prompt),
      przycisk,
      komunikat,
      blad
    ]);
  }

  function kopiuj(tekst, przycisk) {
    if (navigator.clipboard && window.isSecureContext) {
      return navigator.clipboard.writeText(tekst).catch(function () {
        return kopiujZapasowo(tekst, przycisk);
      });
    }
    return kopiujZapasowo(tekst, przycisk);
  }

  // Dla starszych przeglądarek i ramek bez uprawnienia do schowka.
  function kopiujZapasowo(tekst, przycisk) {
    return new Promise(function (udane, nieudane) {
      var pole = el("textarea", { class: "to-schowek", readonly: true, "aria-hidden": "true", tabindex: "-1" });
      pole.value = tekst;
      document.body.appendChild(pole);
      pole.select();
      pole.setSelectionRange(0, tekst.length);
      var ok = false;
      try { ok = document.execCommand("copy"); } catch (e) { ok = false; }
      document.body.removeChild(pole);
      przycisk.focus();
      if (ok) udane();
      else nieudane();
    });
  }

  /* ---------- Start ---------- */

  root.setAttribute("lang", "pl");
  if (document.body.classList.contains("to-strona")) {
    document.title = T.start.tytulStrony;
  }

  // Osadzenie w iframe: wysyłamy stronie-rodzicowi tylko wysokość aplikacji.
  if (window.parent !== window) {
    document.documentElement.classList.add("to-w-ramce");
    if ("ResizeObserver" in window) {
      new ResizeObserver(function () {
        window.parent.postMessage({
          typ: "test-oferty:wysokosc",
          wysokosc: Math.ceil(root.getBoundingClientRect().height)
        }, "*");
      }).observe(root);
    }
  }

  pokazStart();
})();
