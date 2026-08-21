'use strict';

/* =========================================================
   Executive Assistant: System współpracy z CEO
   1. Ruch: tylko dla przeglądarek bez animation-timeline
   2. Przewijanie do kotwic z uwzględnieniem paska górnego
   ========================================================= */

(function () {
    var supportsScrollTimeline =
        window.CSS && CSS.supports && CSS.supports('animation-timeline: view()');

    // Nowoczesne przeglądarki animują natywnie. Klasę dokładamy tylko wtedy,
    // gdy musimy przejąć robotę w JavaScripcie.
    if (!supportsScrollTimeline) {
        document.documentElement.classList.add('js-fallback');
    }

    document.addEventListener('DOMContentLoaded', function () {

        var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        /* ---------- 1. RUCH: ŚCIEŻKA ZAPASOWA ---------- */
        if (!supportsScrollTimeline) {
            var reveal = function (selector, cls, threshold) {
                var nodes = document.querySelectorAll(selector);
                if (reduceMotion || !('IntersectionObserver' in window)) {
                    nodes.forEach(function (el) { el.classList.add(cls); });
                    return;
                }
                var io = new IntersectionObserver(function (entries, obs) {
                    entries.forEach(function (entry) {
                        if (!entry.isIntersecting) return;
                        entry.target.classList.add(cls);
                        obs.unobserve(entry.target);
                    });
                }, { threshold: threshold });
                nodes.forEach(function (el) { io.observe(el); });
            };

            reveal('.rise', 'is-in', 0.05);
            reveal('.mark', 'is-marked', 0.9);

            // Nic ukrytego nie może zostać niewidoczne na dole strony.
            window.addEventListener('scroll', function () {
                if (window.innerHeight + window.pageYOffset < document.body.scrollHeight - 240) return;
                document.querySelectorAll('.rise:not(.is-in)').forEach(function (el) { el.classList.add('is-in'); });
                document.querySelectorAll('.mark:not(.is-marked)').forEach(function (el) { el.classList.add('is-marked'); });
            }, { passive: true });
        }

        /* ---------- 2. ZDJĘCIE PROWADZĄCEJ ---------- */
        // Dopóki plik zdjęcia nie zostanie wgrany, pokazujemy ramkę
        // z instrukcją zamiast pustego kadru.
        var photo = document.getElementById('photo-prowadzaca');
        if (photo) {
            var markEmpty = function () {
                var frame = photo.closest('.photo');
                if (frame) frame.classList.add('photo--empty');
            };
            if (photo.complete && photo.naturalWidth === 0) markEmpty();
            photo.addEventListener('error', markEmpty);
        }

        /* ---------- 3. KOTWICE ---------- */
        // Identyfikatory sekcji zawierają polskie znaki, więc szukamy
        // przez getElementById zamiast querySelector.
        function findTarget(href) {
            if (!href || href.charAt(0) !== '#' || href === '#') return null;
            var id = href.slice(1);
            var el = document.getElementById(id);
            if (el) return el;
            try { return document.getElementById(decodeURIComponent(id)); }
            catch (e) { return null; }
        }

        function barOffset() {
            var bar = document.querySelector('.bar');
            return (bar ? bar.offsetHeight : 0) + 16;
        }

        document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
            anchor.addEventListener('click', function (event) {
                var target = findTarget(this.getAttribute('href'));
                if (!target) return;
                event.preventDefault();

                var top = target.getBoundingClientRect().top + window.pageYOffset - barOffset();
                window.scrollTo({ top: top < 0 ? 0 : top, behavior: reduceMotion ? 'auto' : 'smooth' });

                if (window.history && window.history.replaceState) {
                    window.history.replaceState(null, '', this.getAttribute('href'));
                }
            });
        });
    });
})();
