/**
 * bydopamina – interakcje UX. Bez zależności (jQuery tylko do zdarzeń WooCommerce, jeśli jest), ładowany z defer.
 */
(() => {
	'use strict';

	const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
	const $ = (sel, ctx = document) => ctx.querySelector(sel);
	const $$ = (sel, ctx = document) => Array.from(ctx.querySelectorAll(sel));

	/* 1. Cel linku „Przejdź do treści”. */
	if (!document.getElementById('bd-main')) {
		const main = $('main, .site-main, [data-elementor-type="wp-page"], [data-elementor-type="product"], [data-elementor-type="product-archive"], [data-elementor-type="single-page"]');
		if (main) {
			main.id = 'bd-main';
			main.setAttribute('tabindex', '-1');
		}
	}

	/* 2. Header: stan po przewinięciu; na stronie głównej header nad hero jest przezroczysty. */
	const header = $('.bd-header');
	if (header) {
		const onScroll = () => header.classList.toggle('is-scrolled', window.scrollY > 24);
		onScroll();
		window.addEventListener('scroll', onScroll, { passive: true });
	}

	/* 3. Pojawianie się sekcji – fallback bez animation-timeline. */
	if (!CSS.supports('animation-timeline: view()')) {
		const items = $$('.bd-reveal');
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

	/* 4. Zakładki produktów (WAI-ARIA Tabs: strzałki, Home, End). */
	$$('[data-bd-tabs]').forEach((root) => {
		const tabs = $$('[role="tab"]', root);
		const select = (tab, focus = true) => {
			tabs.forEach((t) => {
				const on = t === tab;
				t.setAttribute('aria-selected', String(on));
				t.tabIndex = on ? 0 : -1;
				document.getElementById(t.getAttribute('aria-controls')).hidden = !on;
			});
			if (focus) tab.focus();
		};
		tabs.forEach((tab, i) => {
			tab.addEventListener('click', () => select(tab, false));
			tab.addEventListener('keydown', (e) => {
				const map = { ArrowRight: i + 1, ArrowLeft: i - 1, Home: 0, End: tabs.length - 1 };
				if (!(e.key in map)) return;
				e.preventDefault();
				select(tabs[(map[e.key] + tabs.length) % tabs.length]);
			});
		});
	});

	/* 5. Okna dialogowe (rozmiarówka) – natywny <dialog>: pułapka fokusu i Esc w przeglądarce. */
	document.addEventListener('click', (e) => {
		const opener = e.target.closest('[data-bd-dialog]');
		if (opener) {
			const dlg = document.getElementById(opener.dataset.bdDialog);
			if (dlg && typeof dlg.showModal === 'function') {
				e.preventDefault();
				dlg.showModal();
				document.documentElement.classList.add('bd-lock');
			}
			return;
		}
		if (e.target.closest('[data-bd-dialog-close]') || e.target.classList.contains('bd-dialog')) {
			const dlg = e.target.closest('dialog');
			if (dlg) dlg.close();
		}
	});
	$$('.bd-dialog').forEach((dlg) => dlg.addEventListener('close', () => document.documentElement.classList.remove('bd-lock')));

	/* 6. Shop the look: punkt ↔ pozycja na liście. */
	$$('.bd-look').forEach((look) => {
		const activate = (id, on) => {
			const item = document.getElementById(id);
			const dot = $(`[data-bd-look="${id}"]`, look);
			item?.classList.toggle('is-active', on);
			dot?.classList.toggle('is-active', on);
		};
		$$('.bd-look__dot', look).forEach((dot) => {
			const id = dot.dataset.bdLook;
			['mouseenter', 'focus'].forEach((ev) => dot.addEventListener(ev, () => activate(id, true)));
			['mouseleave', 'blur'].forEach((ev) => dot.addEventListener(ev, () => activate(id, false)));
			dot.addEventListener('click', () => {
				const link = $(`#${CSS.escape(id)} a`);
				if (!link) return;
				// Na dotyku pierwsze tknięcie podświetla, drugie przenosi do produktu.
				if (dot.classList.contains('is-armed')) {
					link.click();
				} else {
					$$('.bd-look__dot', look).forEach((d) => d.classList.remove('is-armed'));
					dot.classList.add('is-armed');
					document.getElementById(id)?.scrollIntoView({ block: 'nearest', behavior: reduceMotion ? 'auto' : 'smooth' });
				}
			});
		});
		$$('.bd-look__item', look).forEach((item) => {
			item.addEventListener('mouseenter', () => activate(item.id, true));
			item.addEventListener('mouseleave', () => activate(item.id, false));
		});
	});

	/* 7. Sticky „Dodaj do koszyka” na mobile – gdy główny formularz jest nad widokiem. */
	const sticky = $('.bd-sticky-atc');
	const mainForm = $('form.cart');
	if (sticky && mainForm && 'IntersectionObserver' in window) {
		sticky.hidden = false;
		const io = new IntersectionObserver(([entry]) => {
			const passed = !entry.isIntersecting && entry.boundingClientRect.top < 0;
			sticky.classList.toggle('is-visible', passed);
			sticky.inert = !passed;
		});
		io.observe(mainForm);

		$('[data-bd-scroll-atc]', sticky)?.addEventListener('click', () => {
			const btn = $('.single_add_to_cart_button', mainForm);
			const isSimple = !mainForm.classList.contains('variations_form');
			if (isSimple && btn && !btn.disabled) {
				btn.click();
				return;
			}
			mainForm.scrollIntoView({ behavior: reduceMotion ? 'auto' : 'smooth', block: 'center' });
			$('select, input:not([type=hidden])', mainForm)?.focus({ preventScroll: true });
		});
	}

	/* 8. „Szukaj” w dolnym pasku otwiera wyszukiwarkę z headera. */
	document.addEventListener('click', (ev) => {
		if (!ev.target.closest('[data-bd-open-search]')) return;
		const toggle = $('.elementor-search-form__toggle, .e-search-submit');
		if (toggle) {
			ev.preventDefault();
			toggle.click();
		}
	});

	/* 9. WooCommerce: licznik w dolnym pasku + przeliczenie checkoutu po zaznaczeniu pakowania na prezent. */
	const syncCount = () => $$('.bd-mnav__count').forEach((el) => {
		el.dataset.count = String(parseInt(el.textContent, 10) || 0);
	});
	syncCount();
	if (window.jQuery) {
		const body = window.jQuery(document.body);
		body.on('wc_fragments_refreshed wc_fragments_loaded added_to_cart removed_from_cart', syncCount);
		body.on('change', 'input[name="bd_giftwrap"]', () => body.trigger('update_checkout'));
	}
})();
