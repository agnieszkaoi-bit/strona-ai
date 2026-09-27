/**
 * bydopamina – drobne usprawnienia UX. Bez zależności (bez jQuery), ładowany z defer.
 */
(() => {
	'use strict';

	const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

	/* 1. Cel linku „Przejdź do treści”: pierwszy element po headerze. */
	if (!document.getElementById('bd-main')) {
		const main = document.querySelector('main, .site-main, [data-elementor-type="wp-page"], [data-elementor-type="product"], [data-elementor-type="product-archive"], [data-elementor-type="single-page"]');
		if (main) {
			main.id = 'bd-main';
			main.setAttribute('tabindex', '-1');
		}
	}

	/* 2. Header: klasa po przewinięciu (cień/linia). */
	const header = document.querySelector('.bd-header');
	if (header) {
		const onScroll = () => header.classList.toggle('is-scrolled', window.scrollY > 8);
		onScroll();
		window.addEventListener('scroll', onScroll, { passive: true });
	}

	/* 3. Pojawianie się sekcji – fallback dla przeglądarek bez animation-timeline. */
	if (!CSS.supports('animation-timeline: view()')) {
		const items = document.querySelectorAll('.bd-reveal');
		if (reduceMotion || !('IntersectionObserver' in window)) {
			items.forEach((el) => el.classList.add('is-in'));
		} else {
			const io = new IntersectionObserver((entries) => {
				entries.forEach((e) => {
					if (e.isIntersecting) {
						e.target.classList.add('is-in');
						io.unobserve(e.target);
					}
				});
			}, { rootMargin: '0px 0px -10% 0px' });
			items.forEach((el) => io.observe(el));
		}
	}

	/* 4. Sticky „Dodaj do koszyka” na mobile – gdy główny przycisk jest poza ekranem. */
	const sticky = document.querySelector('.bd-sticky-atc');
	const mainForm = document.querySelector('form.cart');
	if (sticky && mainForm && 'IntersectionObserver' in window) {
		sticky.hidden = false;
		const io = new IntersectionObserver(([entry]) => {
			// Pokaż dopiero, gdy formularz jest POWYŻEJ widoku (użytkownik zjechał niżej).
			const passed = !entry.isIntersecting && entry.boundingClientRect.top < 0;
			sticky.classList.toggle('is-visible', passed);
			sticky.setAttribute('aria-hidden', passed ? 'false' : 'true');
		});
		io.observe(mainForm);

		sticky.querySelector('[data-bd-scroll-atc]')?.addEventListener('click', () => {
			const btn = mainForm.querySelector('.single_add_to_cart_button');
			const isSimple = !mainForm.classList.contains('variations_form');
			if (isSimple && btn && !btn.disabled) {
				btn.click();
				return;
			}
			mainForm.scrollIntoView({ behavior: reduceMotion ? 'auto' : 'smooth', block: 'center' });
			mainForm.querySelector('select, input:not([type=hidden])')?.focus({ preventScroll: true });
		});
	}

	/* 5. Ikona „Szukaj” w dolnym pasku otwiera pełnoekranową wyszukiwarkę z headera. */
	document.addEventListener('click', (ev) => {
		const trigger = ev.target.closest('[data-bd-open-search]');
		if (!trigger) return;
		const toggle = document.querySelector('.elementor-search-form__toggle, .e-search-submit');
		if (toggle) {
			ev.preventDefault();
			toggle.click();
		}
	});

	/* 6. Licznik koszyka w dolnym pasku – ukryj zero (fragmenty WooCommerce podmieniają element). */
	const syncCount = () => {
		document.querySelectorAll('.bd-mnav__count').forEach((el) => {
			el.dataset.count = String(parseInt(el.textContent, 10) || 0);
		});
	};
	syncCount();
	document.body.addEventListener('wc_fragments_refreshed', syncCount);
	document.body.addEventListener('wc_fragments_loaded', syncCount);
	// jQuery triggeruje zdarzenia WooCommerce – nasłuchujemy przez jQuery, jeśli jest na stronie.
	if (window.jQuery) {
		window.jQuery(document.body).on('wc_fragments_refreshed wc_fragments_loaded added_to_cart removed_from_cart', syncCount);
	}
})();
