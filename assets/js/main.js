/* ARK Consulting — main.js — nawigacja, animacje, FAQ, karuzela, licznik */
document.addEventListener('DOMContentLoaded', function () {

  /* Rok w stopce */
  var yr = document.getElementById('yr');
  if (yr) yr.textContent = new Date().getFullYear();

  /* Znacznik czasu antyspamowy dla formularza kontaktowego (patrz wyslij-wiadomosc.php) */
  var formTs = document.getElementById('formTs');
  if (formTs) formTs.value = String(Date.now());

  /* Nav scroll — passive listener */
  var nav = document.getElementById('nav');
  if (nav) {
    window.addEventListener('scroll', function () {
      nav.classList.toggle('stuck', window.scrollY > 30);
    }, { passive: true });
  }

  /* Reveal — IntersectionObserver */
  if ('IntersectionObserver' in window) {
    var ro = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) {
        if (e.isIntersecting) { e.target.classList.add('on'); ro.unobserve(e.target); }
      });
    }, { threshold: .1, rootMargin: '0px 0px -40px 0px' });
    document.querySelectorAll('.r').forEach(function (el) { ro.observe(el); });
  } else {
    document.querySelectorAll('.r').forEach(function (el) { el.classList.add('on'); });
  }

  /* FAQ accordion */
  document.querySelectorAll('.faq-q').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var item = btn.closest('.faq-item');
      var open = item.classList.contains('open');
      item.parentElement.querySelectorAll('.faq-item.open').forEach(function (i) {
        i.classList.remove('open');
        i.querySelector('.faq-q').setAttribute('aria-expanded', 'false');
      });
      if (!open) { item.classList.add('open'); btn.setAttribute('aria-expanded', 'true'); }
    });
  });

  /* Hamburger */
  var ham = document.getElementById('ham');
  var mob = document.getElementById('mob');
  if (ham && mob) {
    ham.addEventListener('click', function () {
      var open = mob.classList.toggle('open');
      ham.setAttribute('aria-expanded', String(open));
    });
    mob.querySelectorAll('a').forEach(function (a) {
      a.addEventListener('click', function () {
        mob.classList.remove('open');
        ham.setAttribute('aria-expanded', 'false');
      });
    });
  }

  /* Smooth scroll dla kotwic na tej samej stronie */
  document.querySelectorAll('a[href^="#"]').forEach(function (a) {
    a.addEventListener('click', function (e) {
      var id = a.getAttribute('href').slice(1);
      if (!id) return;
      var el = document.getElementById(id);
      if (el) { e.preventDefault(); el.scrollIntoView({ behavior: 'smooth' }); }
    });
  });

  /* ── Karuzela referencji ── */
  (function () {
    var track = document.getElementById('ctrack');
    var dotsWrap = document.getElementById('cdots');
    if (!track) return;

    var cards = track.querySelectorAll('.ref-card');
    var perView = window.innerWidth <= 600 ? 1 : window.innerWidth <= 900 ? 2 : 3;
    var total = cards.length;
    var current = 0;
    var timer;

    function pageCount() { return Math.ceil(total / perView); }

    function buildDots() {
      dotsWrap.innerHTML = '';
      for (var i = 0; i < pageCount(); i++) {
        var d = document.createElement('button');
        d.className = 'carousel-dot' + (i === current ? ' active' : '');
        d.setAttribute('role', 'tab');
        d.setAttribute('aria-label', 'Referencja ' + (i * perView + 1));
        d.setAttribute('aria-selected', String(i === current));
        (function (idx) { d.addEventListener('click', function () { goTo(idx); }); })(i);
        dotsWrap.appendChild(d);
      }
    }

    function goTo(idx) {
      current = Math.max(0, Math.min(idx, pageCount() - 1));
      var cardW = cards[0].getBoundingClientRect().width;
      var gap = parseFloat(getComputedStyle(track).gap) || 24;
      track.style.transform = 'translateX(-' + (current * (cardW + gap) * perView) + 'px)';
      dotsWrap.querySelectorAll('.carousel-dot').forEach(function (d, i) {
        d.classList.toggle('active', i === current);
        d.setAttribute('aria-selected', String(i === current));
      });
    }

    function next() { goTo(current + 1 >= pageCount() ? 0 : current + 1); }
    function prev() { goTo(current - 1 < 0 ? pageCount() - 1 : current - 1); }

    var nextBtn = document.getElementById('cnext');
    var prevBtn = document.getElementById('cprev');
    if (nextBtn) nextBtn.addEventListener('click', function () { clearInterval(timer); next(); autoPlay(); });
    if (prevBtn) prevBtn.addEventListener('click', function () { clearInterval(timer); prev(); autoPlay(); });

    function autoPlay() { timer = setInterval(next, 5000); }

    var tx = 0;
    track.addEventListener('touchstart', function (e) { tx = e.touches[0].clientX; }, { passive: true });
    track.addEventListener('touchend', function (e) {
      var dx = tx - e.changedTouches[0].clientX;
      if (Math.abs(dx) > 40) { clearInterval(timer); dx > 0 ? next() : prev(); autoPlay(); }
    }, { passive: true });

    var wrap = track.closest('.carousel-wrap');
    wrap.addEventListener('mouseenter', function () { clearInterval(timer); });
    wrap.addEventListener('mouseleave', autoPlay);

    var resizeTimer;
    window.addEventListener('resize', function () {
      clearTimeout(resizeTimer);
      resizeTimer = setTimeout(function () {
        perView = window.innerWidth <= 600 ? 1 : window.innerWidth <= 900 ? 2 : 3;
        current = 0;
        buildDots();
        goTo(0);
      }, 200);
    }, { passive: true });

    buildDots();
    autoPlay();
  })();

  /* ── Licznik (count-up) ── */
  (function () {
    var els = document.querySelectorAll('.countup');
    if (!els.length) return;
    var fmt = function (n) { return n >= 1000 ? Math.floor(n / 1000) + ' 000' : String(n); };
    var obs = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) {
        if (!e.isIntersecting) return;
        var el = e.target;
        var target = parseInt(el.dataset.target, 10);
        var suffix = el.dataset.suffix || '';
        var dur = 1600;
        var t0 = performance.now();
        (function tick(now) {
          var p = Math.min((now - t0) / dur, 1);
          var ease = 1 - Math.pow(1 - p, 3);
          el.textContent = fmt(Math.round(ease * target)) + suffix;
          if (p < 1) requestAnimationFrame(tick);
        })(t0);
        obs.unobserve(el);
      });
    }, { threshold: .5 });
    els.forEach(function (el) { obs.observe(el); });
  })();

});
