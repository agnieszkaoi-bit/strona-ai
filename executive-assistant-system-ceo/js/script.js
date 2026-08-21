'use strict';

/* =========================================================
   SYSTEM — officeinfluencers.pl/executive-assistant-system-ceo
   1. Zakreślacz: kluczowe zdania „zakreślają się" przy scrollu
   2. Delikatne wejście sekcji
   3. Przewijanie do kotwic z uwzględnieniem paska górnego
   ========================================================= */

document.addEventListener('DOMContentLoaded', function () {

    var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    /* ---------- 1. ZAKREŚLACZ ---------- */
    var marks = document.querySelectorAll('.mark');

    if (reduceMotion || !('IntersectionObserver' in window)) {
        marks.forEach(function (el) { el.classList.add('is-marked'); });
    } else {
        var markObserver = new IntersectionObserver(function (entries, obs) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) return;
                entry.target.classList.add('is-marked');
                obs.unobserve(entry.target);
            });
        }, { threshold: 0.9 });

        marks.forEach(function (el) { markObserver.observe(el); });
    }

    /* ---------- 2. WEJŚCIE SEKCJI ---------- */
    var risers = document.querySelectorAll('.rise');

    if (reduceMotion || !('IntersectionObserver' in window)) {
        risers.forEach(function (el) { el.classList.add('is-in'); });
    } else {
        var riseObserver = new IntersectionObserver(function (entries, obs) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) return;
                entry.target.classList.add('is-in');
                obs.unobserve(entry.target);
            });
        }, { threshold: 0.05 });

        risers.forEach(function (el) { riseObserver.observe(el); });
    }

    /* Zabezpieczenie: element ukryty do animacji nigdy nie może zostać
       niewidoczny. Po dojechaniu na dół strony odsłaniamy resztę. */
    window.addEventListener('scroll', function () {
        var atBottom = window.innerHeight + window.pageYOffset >= document.body.scrollHeight - 240;
        if (!atBottom) return;
        document.querySelectorAll('.rise:not(.is-in)').forEach(function (el) { el.classList.add('is-in'); });
        document.querySelectorAll('.mark:not(.is-marked)').forEach(function (el) { el.classList.add('is-marked'); });
    }, { passive: true });

    /* ---------- 3. KOTWICE ---------- */
    // Identyfikatory sekcji zawierają polskie znaki (np. #najczęstsze-pytania),
    // dlatego szukamy przez getElementById, a nie querySelector.
    function findTarget(rawHref) {
        if (!rawHref || rawHref.charAt(0) !== '#' || rawHref === '#') return null;
        var id = rawHref.slice(1);
        var el = document.getElementById(id);
        if (el) return el;
        try {
            return document.getElementById(decodeURIComponent(id));
        } catch (e) {
            return null;
        }
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
            window.scrollTo({
                top: top < 0 ? 0 : top,
                behavior: reduceMotion ? 'auto' : 'smooth'
            });

            // Kotwica ma zostać w adresie, ale bez skoku wywołanego przez przeglądarkę.
            if (window.history && window.history.replaceState) {
                window.history.replaceState(null, '', this.getAttribute('href'));
            }
        });
    });
});
