'use strict';

document.addEventListener('DOMContentLoaded', () => {

    // 1. ODLICZANIE EARLY BIRD
    const targetDate = new Date("2026-09-16T23:59:59").getTime();
    const countdownEl = document.getElementById("countdown");

    if (countdownEl) {
        const updateCountdown = () => {
            const now = new Date().getTime();
            const distance = targetDate - now;

            if (distance < 0) {
                countdownEl.innerHTML = "Oferta zakończona";
                return;
            }

            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));

            countdownEl.innerHTML = `${days}d ${hours}h ${minutes}m`;
        };

        updateCountdown();
        setInterval(updateCountdown, 1000 * 60);
    }

    // 2. DELIKATNE ANIMACJE SCROLLOWANIA
    const animatedElements = document.querySelectorAll('.animate-up');
    const observerOptions = {
        root: null,
        rootMargin: '0px',
        threshold: 0.15
    };

    const observer = new IntersectionObserver((entries, obs) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                obs.unobserve(entry.target);
            }
        });
    }, observerOptions);

    animatedElements.forEach(el => observer.observe(el));

    // 3. PŁYNNE PRZEWIJANIE DO KOTWIC
    const anchorLinks = document.querySelectorAll('a[href^="#"]');
    const headerElement = document.querySelector('.site-header');
    const promoBarElement = document.querySelector('.promo-bar');

    let headerHeight = 0;
    if (headerElement) headerHeight += headerElement.offsetHeight;
    if (promoBarElement) headerHeight += promoBarElement.offsetHeight;

    anchorLinks.forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;

            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                e.preventDefault();

                const elementPosition = targetElement.getBoundingClientRect().top;
                const offsetPosition = elementPosition + window.pageYOffset - headerHeight - 20;

                window.scrollTo({
                    top: offsetPosition,
                    behavior: "smooth"
                });
            }
        });
    });

    // 4. ŚLEDZENIE KLIKNIĘĆ W BLOK B2B I WYSYŁANIE POWIADOMIEŃ
    const b2bLinks = document.querySelectorAll('.b2b-link');

    b2bLinks.forEach(link => {
        link.addEventListener('click', () => {
            const contactType = link.href.includes('mailto') ? 'E-mail' : 'Telefon';
            const contactValue = link.innerText;

            const webhookUrl = 'https://hook.eu2.make.com/5kusvotxsxutkn3hoxgbfv8dxwwqb6g0';

            fetch(webhookUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    event: 'B2B_Contact_Click',
                    type: contactType,
                    value: contactValue,
                    time: new Date().toLocaleString('pl-PL')
                })
            }).catch(error => console.error('Błąd wysyłania webhooka:', error));
        });
    });
});
