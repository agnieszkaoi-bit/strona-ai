/* ═══════════════════════════════════════════════════════════════
   ARK Consulting — cookie-consent.js
   Baner zgody na pliki cookies zgodny z RODO / Prawem telekomunikacyjnym.
   Kategorie: niezbędne (zawsze aktywne), analityczne, marketingowe.
   Zapisuje decyzję w localStorage i integruje się z Google Consent Mode v2
   (jeśli na stronie zostanie później dodany Google Analytics / Google Ads /
   Meta Pixel — sam banner nie ładuje żadnych narzędzi trzecich).
   ═══════════════════════════════════════════════════════════════ */
(function () {
  'use strict';

  var STORAGE_KEY = 'ark_cookie_consent_v1';

  function getConsent() {
    try {
      var raw = localStorage.getItem(STORAGE_KEY);
      return raw ? JSON.parse(raw) : null;
    } catch (e) { return null; }
  }

  function setConsent(consent) {
    consent.timestamp = new Date().toISOString();
    try { localStorage.setItem(STORAGE_KEY, JSON.stringify(consent)); } catch (e) {}
    applyConsent(consent);
  }

  /* Google Consent Mode v2 — aktualizuje flagi, jeśli gtag jest obecny.
     Nie ładuje niczego samodzielnie — wyłącznie ustawia zgody dla skryptów,
     które ewentualnie zostaną dodane osobno (np. GA4, Google Ads, GTM). */
  function applyConsent(consent) {
    window.dataLayer = window.dataLayer || [];
    function gtag() { window.dataLayer.push(arguments); }
    gtag('consent', 'update', {
      analytics_storage: consent.analytics ? 'granted' : 'denied',
      ad_storage: consent.marketing ? 'granted' : 'denied',
      ad_user_data: consent.marketing ? 'granted' : 'denied',
      ad_personalization: consent.marketing ? 'granted' : 'denied'
    });
    document.dispatchEvent(new CustomEvent('arkCookieConsentUpdated', { detail: consent }));
  }

  function buildBanner() {
    var wrap = document.createElement('div');
    wrap.innerHTML =
      '<div class="cookie-banner" id="cookieBanner" role="dialog" aria-live="polite" aria-label="Zgoda na pliki cookies">' +
        '<div class="cookie-inner">' +
          '<div class="cookie-text">' +
            '<h4>Ta strona używa plików cookies</h4>' +
            '<p>Korzystamy z cookies niezbędnych do działania serwisu oraz — za Twoją zgodą — analitycznych i marketingowych, aby lepiej dopasować ofertę szkoleń. Szczegóły w <a href="/cookies/">Polityce cookies</a> i <a href="/polityka-prywatnosci/">Polityce prywatności</a>.</p>' +
          '</div>' +
          '<div class="cookie-actions">' +
            '<button type="button" class="cookie-btn cookie-settings" id="cookieSettingsBtn">Ustawienia</button>' +
            '<button type="button" class="cookie-btn cookie-reject" id="cookieRejectBtn">Tylko niezbędne</button>' +
            '<button type="button" class="cookie-btn cookie-accept" id="cookieAcceptBtn">Akceptuję wszystkie</button>' +
          '</div>' +
        '</div>' +
      '</div>' +
      '<div class="cookie-modal-overlay" id="cookieModalOverlay">' +
        '<div class="cookie-modal" role="dialog" aria-modal="true" aria-labelledby="cookieModalTitle">' +
          '<h3 id="cookieModalTitle">Ustawienia prywatności</h3>' +
          '<p style="font-size:.83rem;color:var(--muted);">Wybierz, na jakie kategorie plików cookies wyrażasz zgodę. Zgodę możesz w każdej chwili zmienić w stopce strony.</p>' +
          '<div class="cookie-cat">' +
            '<div class="cookie-cat-head">' +
              '<h4>Niezbędne</h4>' +
              '<label class="switch"><input type="checkbox" checked disabled><span class="slider"></span></label>' +
            '</div>' +
            '<p>Wymagane do podstawowego działania strony (np. zapamiętanie Twoich wyborów). Nie można ich wyłączyć.</p>' +
          '</div>' +
          '<div class="cookie-cat">' +
            '<div class="cookie-cat-head">' +
              '<h4>Analityczne</h4>' +
              '<label class="switch"><input type="checkbox" id="ccAnalytics"><span class="slider"></span></label>' +
            '</div>' +
            '<p>Pomagają nam zrozumieć, jak użytkownicy korzystają ze strony (np. Google Analytics) — dane zbierane anonimowo.</p>' +
          '</div>' +
          '<div class="cookie-cat">' +
            '<div class="cookie-cat-head">' +
              '<h4>Marketingowe</h4>' +
              '<label class="switch"><input type="checkbox" id="ccMarketing"><span class="slider"></span></label>' +
            '</div>' +
            '<p>Używane do wyświetlania trafniejszych reklam naszych szkoleń (np. Meta Ads, Google Ads).</p>' +
          '</div>' +
          '<div class="cookie-modal-actions">' +
            '<button type="button" class="btn btn-outline" id="cookieSaveBtn">Zapisz wybór</button>' +
            '<button type="button" class="btn btn-solid" id="cookieAcceptAllBtn">Akceptuję wszystkie</button>' +
          '</div>' +
        '</div>' +
      '</div>';
    document.body.appendChild(wrap);
  }

  function openModal() { document.getElementById('cookieModalOverlay').classList.add('show'); }
  function closeModal() { document.getElementById('cookieModalOverlay').classList.remove('show'); }
  function hideBanner() { document.getElementById('cookieBanner').classList.remove('show'); }
  function showBanner() { document.getElementById('cookieBanner').classList.add('show'); }

  function init() {
    buildBanner();

    var existing = getConsent();
    if (existing) {
      applyConsent(existing);
    } else {
      /* Domyślnie (przed decyzją) — wszystko poza niezbędnymi jest zablokowane */
      applyConsent({ necessary: true, analytics: false, marketing: false });
      setTimeout(showBanner, 400);
    }

    document.getElementById('cookieAcceptBtn').addEventListener('click', function () {
      setConsent({ necessary: true, analytics: true, marketing: true });
      hideBanner();
    });
    document.getElementById('cookieRejectBtn').addEventListener('click', function () {
      setConsent({ necessary: true, analytics: false, marketing: false });
      hideBanner();
    });
    document.getElementById('cookieSettingsBtn').addEventListener('click', function () {
      var c = getConsent() || { analytics: false, marketing: false };
      document.getElementById('ccAnalytics').checked = !!c.analytics;
      document.getElementById('ccMarketing').checked = !!c.marketing;
      openModal();
    });
    document.getElementById('cookieModalOverlay').addEventListener('click', function (e) {
      if (e.target === this) closeModal();
    });
    document.getElementById('cookieSaveBtn').addEventListener('click', function () {
      setConsent({
        necessary: true,
        analytics: document.getElementById('ccAnalytics').checked,
        marketing: document.getElementById('ccMarketing').checked
      });
      closeModal();
      hideBanner();
    });
    document.getElementById('cookieAcceptAllBtn').addEventListener('click', function () {
      setConsent({ necessary: true, analytics: true, marketing: true });
      closeModal();
      hideBanner();
    });

    /* Link "Ustawienia cookies" w stopce (jeśli obecny na stronie) otwiera ponownie panel */
    document.querySelectorAll('[data-cookie-settings]').forEach(function (el) {
      el.addEventListener('click', function (e) {
        e.preventDefault();
        var c = getConsent() || { analytics: false, marketing: false };
        document.getElementById('ccAnalytics').checked = !!c.analytics;
        document.getElementById('ccMarketing').checked = !!c.marketing;
        openModal();
      });
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
