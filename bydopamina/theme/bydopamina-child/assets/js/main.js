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

	/* 10. „Dobierz komplet”: suma zaznaczonych + dodanie wszystkich do koszyka jednym kliknięciem. */
	$$('[data-bd-set]').forEach((set) => {
		const boxes = $$('input[type="checkbox"]', set);
		const totalEl = $('[data-bd-set-total]', set);
		const btn = $('[data-bd-set-add]', set);
		const msg = $('.bd-set__msg', set);
		const currency = set.dataset.currency || 'zł';
		const fmt = (n) => n.toLocaleString('pl-PL', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' ' + currency;
		const chosen = () => boxes.filter((b) => b.checked);
		const update = () => {
			const sum = chosen().reduce((acc, b) => acc + (parseFloat(b.dataset.price) || 0), 0);
			totalEl.textContent = fmt(sum);
			btn.disabled = chosen().length === 0;
		};
		boxes.forEach((b) => b.addEventListener('change', update));
		update();

		btn.addEventListener('click', async () => {
			const params = window.wc_add_to_cart_params;
			if (!params) return;
			const url = params.wc_ajax_url.toString().replace('%%endpoint%%', 'add_to_cart');
			btn.disabled = true;
			btn.setAttribute('aria-busy', 'true');
			msg.textContent = '';
			let added = 0;
			for (const box of chosen()) {
				const data = new URLSearchParams({ product_id: box.value, quantity: '1' });
				try {
					const res = await fetch(url, { method: 'POST', body: data, credentials: 'same-origin' });
					const json = await res.json();
					if (json && !json.error) added += 1;
				} catch (e) { /* kolejny produkt */ }
			}
			btn.removeAttribute('aria-busy');
			btn.disabled = false;
			msg.textContent = added ? `Dodano do koszyka: ${added}` : 'Nie udało się dodać. Spróbuj ponownie.';
			if (added && window.jQuery) {
				// Odśwież mini-koszyk i otwórz go (Elementor nasłuchuje added_to_cart).
				window.jQuery(document.body).trigger('wc_fragment_refresh').trigger('added_to_cart', [{}, '', window.jQuery(btn)]);
			}
		});
	});
	/* 11. Karuzele produktów: strzałki przewijają o szerokość widoku; ukryte na początku/końcu. */
	$$('[data-bd-carousel]').forEach((wrap) => {
		const track = $('ul.products', wrap);
		if (!track) return;
		const mk = (dir, label, glyph) => {
			const b = document.createElement('button');
			b.type = 'button';
			b.className = `bd-carousel__arrow bd-carousel__arrow--${dir}`;
			b.setAttribute('aria-label', label);
			b.innerHTML = `<span aria-hidden="true">${glyph}</span>`;
			b.addEventListener('click', () => track.scrollBy({ left: (dir === 'next' ? 1 : -1) * track.clientWidth * 0.9, behavior: reduceMotion ? 'auto' : 'smooth' }));
			wrap.appendChild(b);
			return b;
		};
		const prev = mk('prev', 'Poprzednie produkty', '‹');
		const next = mk('next', 'Następne produkty', '›');
		const sync = () => {
			prev.hidden = track.scrollLeft < 4;
			next.hidden = track.scrollLeft + track.clientWidth >= track.scrollWidth - 4;
		};
		track.addEventListener('scroll', sync, { passive: true });
		window.addEventListener('resize', sync);
		sync();
	});
})();
