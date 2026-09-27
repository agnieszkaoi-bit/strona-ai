#!/usr/bin/env python3
"""
Generuje szablony Elementora (JSON) dla bydopamina.pl do katalogu elementor-templates/.

Uruchom:  python3 tools/build_templates.py
Import:   WordPress → Szablony → Kreator motywu / Zapisane szablony → „Importuj szablony”.

Szablony używają kontenerów Flexbox (Elementor 3.6+, domyślnie włączone) i widgetów
Elementor Pro + WooCommerce. Wygląd pochodzi głównie z klas bd-* motywu potomnego,
dzięki czemu zmiana marki = zmiana tokens.css, a nie edycja 10 szablonów.
"""
import json
import random
from pathlib import Path

OUT = Path(__file__).resolve().parent.parent / "elementor-templates"
_rng = random.Random(2026)  # deterministyczne ID → czytelne diffy w git


def _id():
    return "%07x" % _rng.getrandbits(28)


def pad(t, r=None, b=None, l=None, unit="px"):
    r = t if r is None else r
    b = t if b is None else b
    l = r if l is None else l
    return {"unit": unit, "top": str(t), "right": str(r), "bottom": str(b), "left": str(l), "isLinked": len({t, r, b, l}) == 1}


def gap(n):
    return {"column": str(n), "row": str(n), "isLinked": True, "unit": "px", "size": n}


def size(n, unit="px"):
    return {"unit": unit, "size": n, "sizes": []}


def con(*children, inner=False, cls="", direction="column", boxed=True, tag=None, **s):
    """Kontener flex. Skróty: justify, align, gap, wrap, width(+_tablet/_mobile), pad(+_mobile), bg, minh."""
    settings = {
        "content_width": "boxed" if boxed else "full",
        "flex_direction": direction,
    }
    if boxed:
        settings["boxed_width"] = size(1360)
    if cls:
        settings["css_classes"] = cls
    if tag:
        settings["html_tag"] = tag
    mapping = {
        "justify": "flex_justify_content",
        "align": "flex_align_items",
        "wrap": "flex_wrap",
    }
    for k, v in s.items():
        if k in mapping:
            settings[mapping[k]] = v
        elif k.startswith("gap"):
            settings["flex_gap" + k[3:]] = gap(v)
        elif k.startswith("width"):
            settings[k] = size(v, "%")
        elif k.startswith("pad"):
            settings["padding" + k[3:]] = v
        elif k == "bg":
            settings["background_background"] = "classic"
            settings["background_color"] = v
        elif k == "minh":
            settings["min_height"] = size(v)
        elif k == "direction_mobile":
            settings["flex_direction_mobile"] = v
        elif k == "direction_tablet":
            settings["flex_direction_tablet"] = v
        else:
            settings[k] = v
    return {"id": _id(), "elType": "container", "isInner": inner, "settings": settings, "elements": list(children)}


def w(widget_type, cls="", **settings):
    if cls:
        settings["_css_classes"] = cls
    return {"id": _id(), "elType": "widget", "widgetType": widget_type, "isInner": False, "settings": settings, "elements": []}


def heading(text, tag="h2", cls="", link=None, align=None):
    s = {"title": text, "header_size": tag}
    if link:
        s["link"] = {"url": link, "is_external": "", "nofollow": ""}
    if align:
        s["align"] = align
    return w("heading", cls, **s)


def text(html, cls=""):
    return w("text-editor", cls, editor=html)


def button(label, url, cls="bd-btn", **s):
    return w("button", cls, text=label, link={"url": url, "is_external": "", "nofollow": ""}, **s)


def shortcode(code, cls=""):
    return w("shortcode", cls, shortcode=code)


def html(markup, cls=""):
    return w("html", cls, html=markup)


def image(cls="", alt_note=""):
    # Pusty URL = placeholder Elementora; podmień w edytorze. Brak zewnętrznych pobrań przy imporcie.
    return w("image", cls, image={"url": "", "id": "", "alt": alt_note}, image_size="large")


def icon_list(items, cls=""):
    return w(
        "icon-list",
        cls,
        view="traditional",
        icon_list=[
            {"_id": _id(), "text": t, "link": {"url": u, "is_external": "", "nofollow": ""}, "selected_icon": {"value": "", "library": ""}}
            for t, u in items
        ],
    )


def template(title, kind, content, page_settings=None):
    return {"version": "0.4", "title": title, "type": kind, "page_settings": page_settings or {}, "content": content}


FULL_PAGE = {"template": "elementor_header_footer", "hide_title": "yes"}
SECTION_PAD = pad(0, 24, 0, 24)

# ---------------------------------------------------------------------------
# HEADER
# ---------------------------------------------------------------------------

def header():
    announcement = con(
        text("<p>Darmowa dostawa od 199 zł · Wysyłka w 24 h · 30 dni na zwrot</p>", "bd-announcement"),
        boxed=False,
        cls="bd-announcement",
        bg="#121212",
        align="center",
        pad=pad(8, 16),
    )
    logo = heading("bydopamina", "div", "bd-logo", link="/")
    logo["settings"].update({
        "typography_typography": "custom",
        "typography_font_family": "Bricolage Grotesque",
        "typography_font_weight": "700",
        "typography_font_size": size(26),
        "typography_letter_spacing": size(-0.8),
    })
    nav = w(
        "nav-menu",
        "",
        menu="",  # po imporcie wybierz menu „Główne”
        layout="horizontal",
        align_items="center",
        pointer="underline",
        animation_line="slide",
        dropdown="tablet",
        toggle="burger",
        full_width="stretch",
        text_align="aside",
        submenu_icon={"value": "", "library": ""},
    )
    icons = con(
        w("search-form", "", skin="full_screen", placeholder="Czego szukasz? np. kubek, bluza…"),
        w("icon", "", selected_icon={"value": "far fa-user", "library": "fa-regular"},
          link={"url": "/moje-konto/", "is_external": "", "nofollow": ""}, view="default",
          _title="Moje konto", aria_label="Moje konto"),
        w("woocommerce-menu-cart", "", icon="bag-medium", items_indicator="bubble", hide_empty_indicator="yes",
          cart_type="side-cart", open_cart="click", automatically_open_cart="yes", show_subtotal="no",
          main_cart_button_padding=pad(0)),
        inner=True, direction="row", align="center", cls="bd-header-icons", boxed=False, gap=4,
    )
    bar = con(
        logo,
        nav,
        icons,
        direction="row",
        justify="space-between",
        align="center",
        wrap="nowrap",
        gap=16,
        cls="bd-header",
        tag="header",
        minh=68,
        pad=pad(0, 24),
        pad_mobile=pad(0, 16),
        sticky="top",
        sticky_on=["desktop", "tablet", "mobile"],
        sticky_effects_offset=size(10),
        z_index=100,
    )
    return template("bydopamina — Header", "header", [announcement, bar])


# ---------------------------------------------------------------------------
# FOOTER
# ---------------------------------------------------------------------------

def footer():
    cols = con(
        con(
            heading("bydopamina", "div", "bd-footer-logo"),
            text('<p class="bd-muted">Małe rzeczy, które robią Ci dzień. Projektujemy i wysyłamy z Polski.</p>'),
            w("social-icons", "", shape="circle", social_icon_list=[
                {"_id": _id(), "social_icon": {"value": "fab fa-instagram", "library": "fa-brands"}, "link": {"url": "https://instagram.com/bydopamina", "is_external": "on", "nofollow": ""}},
                {"_id": _id(), "social_icon": {"value": "fab fa-tiktok", "library": "fa-brands"}, "link": {"url": "https://tiktok.com/@bydopamina", "is_external": "on", "nofollow": ""}},
                {"_id": _id(), "social_icon": {"value": "fab fa-pinterest", "library": "fa-brands"}, "link": {"url": "https://pinterest.com/bydopamina", "is_external": "on", "nofollow": ""}},
            ], icon_color="custom", icon_primary_color="#FFFFFF1A", icon_secondary_color="#FFFFFF"),
            inner=True, boxed=False, width=34, width_tablet=100, gap=16,
        ),
        con(heading("Sklep", "h2", "bd-footer-h"), icon_list([
            ("Nowości", "/sklep/?orderby=date"), ("Bestsellery", "/sklep/?orderby=popularity"),
            ("Promocje", "/promocje/"), ("Karty podarunkowe", "/karta-podarunkowa/")]),
            inner=True, boxed=False, width=22, width_tablet=33, width_mobile=50, gap=12),
        con(heading("Pomoc", "h2", "bd-footer-h"), icon_list([
            ("Dostawa i płatności", "/dostawa-i-platnosci/"), ("Zwroty i reklamacje", "/zwroty/"),
            ("FAQ", "/faq/"), ("Kontakt", "/kontakt/"), ("Śledź zamówienie", "/moje-konto/zamowienia/")]),
            inner=True, boxed=False, width=22, width_tablet=33, width_mobile=50, gap=12),
        con(heading("Kontakt", "h2", "bd-footer-h"), icon_list([
            ("hej@bydopamina.pl", "mailto:hej@bydopamina.pl"), ("+48 000 000 000", "tel:+48000000000"),
            ("pn–pt 9:00–16:00", "")]),
            inner=True, boxed=False, width=22, width_tablet=33, width_mobile=100, gap=12),
        direction="row", wrap="wrap", gap=32, inner=True, boxed=False,
    )
    payments = html(
        '<ul class="bd-payments" aria-label="Metody płatności i dostawy">'
        "<li>BLIK</li><li>Visa</li><li>Mastercard</li><li>Apple Pay</li><li>Google Pay</li>"
        "<li>Przelewy24</li><li>PayPo</li><li>InPost</li><li>DPD</li></ul>"
    )
    wordmark = heading("bydopamina", "div", "bd-footer-wordmark", align="center")
    wordmark["settings"].update({"typography_typography": "custom", "typography_font_family": "Bricolage Grotesque", "typography_font_weight": "700"})
    bottom = con(
        shortcode("© [bd_year] bydopamina.pl"),
        icon_list([("Regulamin", "/regulamin/"), ("Polityka prywatności", "/polityka-prywatnosci/"),
                   ("Cookies", "/polityka-cookies/"), ("Odstąpienie od umowy", "/odstapienie-od-umowy/")]),
        direction="row", justify="space-between", align="center", wrap="wrap", gap=16, inner=True, boxed=False,
        direction_mobile="column",
    )
    bottom["elements"][1]["settings"]["view"] = "inline"
    main = con(
        cols, payments, wordmark, bottom,
        cls="bd-footer bd-dark", tag="footer", bg="#121212", gap=48,
        pad=pad(80, 24, 32, 24), pad_mobile=pad(56, 16, 24, 16),
    )
    mobile_nav = con(shortcode("[bd_mobile_nav]"), boxed=False, pad=pad(0),
                     hide_desktop="hidden-desktop", hide_tablet="hidden-tablet")
    return template("bydopamina — Footer", "footer", [main, mobile_nav])


# ---------------------------------------------------------------------------
# STRONA GŁÓWNA
# ---------------------------------------------------------------------------

def bento_tile(title, url, cls, sub="Zobacz"):
    return con(
        heading(title, "h3", "bd-h3"),
        text(f'<p><a class="bd-link-arrow" href="{url}">{sub}</a></p>'),
        inner=True, boxed=False, cls=f"bd-tile {cls}".strip(), justify="flex-end", pad=pad(24),
        bg="#F2EEE8", background_image={"url": "", "id": ""}, background_position="center center",
        background_size="cover",
    )


def section_head(title, link_label, url):
    return con(
        heading(title, "h2", "bd-h2"),
        text(f'<p><a class="bd-link-arrow" href="{url}">{link_label}</a></p>'),
        direction="row", justify="space-between", align="flex-end", wrap="wrap", gap=16, inner=True, boxed=False,
    )


def products_rail(orderby, rows=1):
    return w(
        "woocommerce-products", "bd-rail",
        columns="4", columns_tablet="3", columns_mobile="2", rows=str(rows), paginate="",
        query_post_type="product", query_orderby=orderby, query_order="desc",
        query_exclude=["current_post"],
    )


REVIEW = (
    '<article class="bd-review"><p class="bd-stars" aria-label="Ocena 5 na 5">★★★★★</p>'
    "<p>„Tu wstaw prawdziwą opinię klienta – najlepiej z konkretem: co kupił, jak szybko dotarło, co go zaskoczyło.”</p>"
    '<p class="bd-muted"><strong>Imię N.</strong> · zweryfikowany zakup</p></article>'
)

FAQ = """<div class="bd-accordion">
<details><summary><strong>Ile trwa dostawa?</strong></summary><p>Zamówienia złożone do 14:00 w dni robocze wysyłamy tego samego dnia. InPost Paczkomat i kurier dostarczają zwykle w 1–2 dni robocze.</p></details>
<details><summary><strong>Jak mogę zapłacić?</strong></summary><p>BLIK, karta, Apple Pay, Google Pay, szybki przelew (Przelewy24) oraz PayPo – kup teraz, zapłać za 30 dni.</p></details>
<details><summary><strong>Jak zwrócić produkt?</strong></summary><p>Masz 30 dni na zwrot bez podawania przyczyny. Wygeneruj etykietę w zakładce „Zwroty” – zwrot pieniędzy do 5 dni roboczych od otrzymania paczki.</p></details>
<details><summary><strong>Czy mogę zmienić zamówienie?</strong></summary><p>Tak, dopóki nie zostało wysłane. Napisz na hej@bydopamina.pl, podając numer zamówienia.</p></details>
</div>"""

MARQUEE_ITEMS = ["Projektowane w Polsce", "Wysyłka w 24 h", "30 dni na zwrot", "Małe serie", "Opakowania bez plastiku"]


def home():
    hero = con(
        con(
            html('<span class="bd-eyebrow">Nowa kolekcja · Jesień 2026</span>'),
            heading('Rzeczy, które robią Ci <span class="bd-highlight">dzień</span>.', "h1", "bd-hero-title"),
            text('<p class="bd-muted" style="font-size:var(--bd-fs-lg);max-width:34ch">Krótki opis marki w jednym zdaniu: co sprzedajesz i dlaczego u Ciebie – konkret, bez ogólników.</p>'),
            con(
                button("Kup teraz", "/sklep/", "bd-btn", size="lg"),
                button("Zobacz nowości", "/sklep/?orderby=date", "bd-btn bd-btn--ghost", size="lg"),
                direction="row", wrap="wrap", gap=12, inner=True, boxed=False,
            ),
            html('<p class="bd-muted" style="font-size:var(--bd-fs-sm);margin:0">★ 4,9/5 · 2 300+ opinii zweryfikowanych klientów</p>'),
            inner=True, boxed=False, width=50, width_tablet=100, gap=24, justify="center",
        ),
        con(
            image("bd-lcp", "Zdjęcie hero – produkt w użyciu"),
            html('<span class="bd-hero__badge">Nowość ✦</span>'),
            inner=True, boxed=False, width=50, width_tablet=100, cls="bd-hero__media", position="relative",
        ),
        direction="row", wrap="wrap", align="center", gap=48, cls="bd-hero bd-section", tag="section",
        pad=pad(40, 24, 64, 24), pad_mobile=pad(16, 16, 48, 16),
    )

    trust = con(shortcode("[bd_trust_badges]"), cls="bd-section--tight bd-surface", tag="section",
                pad=pad(32, 24), pad_mobile=pad(28, 16))

    bento = con(
        section_head("Kategorie", "Wszystkie produkty", "/sklep/"),
        con(
            bento_tile("Bestsellery", "/sklep/?orderby=popularity", "bd-bento__lg", "Kup to, co kochają inni"),
            bento_tile("Nowości", "/sklep/?orderby=date", "bd-tile--pink"),
            bento_tile("Na prezent", "/kategoria-produktu/prezenty/", ""),
            bento_tile("Kategoria 3", "/kategoria-produktu/kategoria-3/", "bd-bento__wide bd-tile--lime"),
            inner=True, boxed=False, cls="bd-bento",
        ),
        cls="bd-section bd-reveal", tag="section", gap=32, pad=SECTION_PAD, pad_mobile=pad(0, 16),
    )

    bestsellers = con(
        section_head("Bestsellery", "Zobacz wszystkie", "/sklep/?orderby=popularity"),
        products_rail("popularity"),
        cls="bd-section bd-reveal", tag="section", gap=32, pad=SECTION_PAD, pad_mobile=pad(0, 16),
    )

    marquee = con(
        html('<div class="bd-marquee" aria-hidden="true"><ul class="bd-marquee__track">'
             + "".join(f"<li>{t}</li>" for t in MARQUEE_ITEMS * 2) + "</ul></div>"
             '<p class="screen-reader-text">' + ", ".join(MARQUEE_ITEMS) + "</p>"),
        boxed=False, pad=pad(0),
    )

    story = con(
        con(image("", "Zdjęcie zespołu / pracowni"), inner=True, boxed=False, width=50, width_tablet=100, cls="bd-hero__media"),
        con(
            html('<span class="bd-eyebrow">Dlaczego bydopamina</span>'),
            heading("Mniej, ale lepiej. I z uśmiechem.", "h2", "bd-h2"),
            text("<p>2–3 zdania o historii marki i wartościach. Ludzie kupują od ludzi – pokaż twarz, pracownię, proces.</p>"),
            icon_list([("Projektujemy i pakujemy w Polsce", ""), ("Krótkie serie – bez nadprodukcji", ""),
                       ("Opakowania z recyklingu, bez plastiku", "")]),
            button("Poznaj nas", "/o-nas/", "bd-btn bd-btn--ghost"),
            inner=True, boxed=False, width=50, width_tablet=100, gap=20, justify="center",
        ),
        direction="row", wrap="wrap", align="center", gap=64, cls="bd-section bd-reveal", tag="section",
        pad=SECTION_PAD, pad_mobile=pad(0, 16),
    )

    new_in = con(
        section_head("Nowości", "Zobacz wszystkie", "/sklep/?orderby=date"),
        products_rail("date"),
        cls="bd-section bd-reveal", tag="section", gap=32, pad=SECTION_PAD, pad_mobile=pad(0, 16),
    )

    reviews = con(
        section_head("Co mówią klienci", "Wszystkie opinie", "/opinie/"),
        con(*[con(html(REVIEW), inner=True, boxed=False, width=33, width_tablet=100) for _ in range(3)],
            direction="row", wrap="wrap", gap=16, inner=True, boxed=False),
        text('<p class="bd-muted" style="font-size:var(--bd-fs-xs)">Opinie publikujemy wyłącznie od osób, które kupiły produkt w naszym sklepie – weryfikujemy je na podstawie numeru zamówienia.</p>'),
        cls="bd-section bd-reveal", tag="section", gap=32, pad=SECTION_PAD, pad_mobile=pad(0, 16),
    )

    newsletter_form = w(
        "form", "",
        form_name="Newsletter",
        form_fields=[
            {"_id": _id(), "custom_id": "email", "field_type": "email", "field_label": "Adres e-mail",
             "placeholder": "twoj@email.pl", "required": "true", "width": "100"},
            {"_id": _id(), "custom_id": "hp", "field_type": "honeypot", "field_label": "", "width": "100"},
            {"_id": _id(), "custom_id": "zgoda", "field_type": "acceptance", "field_label": "",
             "acceptance_text": 'Zgadzam się na otrzymywanie newslettera. Wypiszesz się jednym kliknięciem. <a href="/polityka-prywatnosci/">Polityka prywatności</a>.',
             "required": "true", "width": "100"},
        ],
        show_labels="yes", button_text="Zapisz się i odbierz −10%", button_width="100",
        submit_actions=["save-to-database"],
        success_message="Dzięki! Sprawdź skrzynkę i potwierdź zapis – kod wyślemy od razu.",
        error_message="Coś poszło nie tak. Spróbuj ponownie.",
    )
    newsletter = con(
        con(
            con(
                heading("−10% na pierwsze zakupy", "h2", "bd-h2"),
                text("<p>Nowości i dropy przed wszystkimi. Maks. 2 maile w miesiącu.</p>"),
                inner=True, boxed=False, width=50, width_tablet=100, gap=12,
            ),
            con(newsletter_form, inner=True, boxed=False, width=50, width_tablet=100),
            direction="row", wrap="wrap", align="center", gap=40, inner=True, boxed=False,
            cls="bd-newsletter bd-dark", bg="#121212", pad=pad(56), pad_mobile=pad(32, 20),
        ),
        cls="bd-section bd-reveal", tag="section", pad=SECTION_PAD, pad_mobile=pad(0, 16),
    )

    faq = con(
        con(heading("Pytania i odpowiedzi", "h2", "bd-h2"),
            text('<p><a class="bd-link-arrow" href="/faq/">Wszystkie pytania</a></p>'),
            inner=True, boxed=False, width=40, width_tablet=100, gap=12),
        con(html(FAQ), inner=True, boxed=False, width=60, width_tablet=100),
        direction="row", wrap="wrap", gap=48, cls="bd-section bd-reveal", tag="section",
        pad=pad(0, 24, 120, 24), pad_mobile=pad(0, 16, 64, 16),
    )

    return template("bydopamina — Strona główna", "page",
                    [hero, trust, bento, bestsellers, marquee, story, new_in, reviews, newsletter, faq], FULL_PAGE)


# ---------------------------------------------------------------------------
# KARTA PRODUKTU (Single Product)
# ---------------------------------------------------------------------------

def accordion_item(title, *widgets):
    return title, con(*widgets, inner=True, boxed=False, pad=pad(0, 0, 16, 0))


def nested_accordion(items):
    return {
        "id": _id(), "elType": "widget", "widgetType": "nested-accordion", "isInner": False,
        "settings": {
            "items": [{"_id": _id(), "item_title": t} for t, _ in items],
            "default_state": "expanded",  # pierwszy element otwarty
            "max_items_expended": "one",
            "_css_classes": "bd-accordion",
        },
        "elements": [c for _, c in items],
    }


def product():
    gallery = con(
        w("woocommerce-product-images", "bd-pdp-gallery", sale_flash="yes"),
        inner=True, boxed=False, width=58, width_tablet=100,
    )
    summary = con(
        w("woocommerce-breadcrumb", ""),
        w("woocommerce-product-title", "bd-pdp-title", header_size="h1"),
        w("woocommerce-product-rating", ""),
        w("woocommerce-product-price", "bd-pdp-price"),
        w("woocommerce-product-short-description", "bd-muted"),
        w("woocommerce-product-add-to-cart", "", show_quantity="yes", layout="stacked"),
        shortcode("[bd_delivery_eta]"),
        shortcode("[bd_free_shipping_bar]"),
        shortcode('[bd_trust_badges variant="stack"]'),
        nested_accordion([
            accordion_item("Opis", w("woocommerce-product-content", "")),
            accordion_item("Szczegóły i wymiary", w("woocommerce-product-additional-information", "", show_heading="")),
            accordion_item("Dostawa i zwroty", text(
                "<p><strong>InPost Paczkomat</strong> – 1–2 dni robocze · <strong>Kurier DPD</strong> – 1–2 dni robocze. "
                "Darmowa dostawa od 199 zł.</p><p>30 dni na zwrot bez podawania przyczyny. "
                '<a href="/zwroty/">Jak zwrócić produkt</a></p>')),
            accordion_item("Bezpieczeństwo produktu", text(
                "<p><strong>Producent:</strong> [nazwa, adres, e-mail]<br><strong>Podmiot odpowiedzialny w UE:</strong> [jeśli inny]<br>"
                "Ostrzeżenia i instrukcje: [zgodnie z GPSR – uzupełnij dla produktu lub użyj pola własnego].</p>")),
            accordion_item("Opinie", shortcode("[bd_product_reviews]")),
        ]),
        inner=True, boxed=False, width=42, width_tablet=100, gap=18, cls="bd-sticky-col",
    )
    top = con(gallery, summary, direction="row", wrap="wrap", gap=48, tag="section",
              pad=pad(24, 24, 80, 24), pad_mobile=pad(8, 16, 48, 16))
    upsell = con(
        heading("Pasuje do tego", "h2", "bd-h2"),
        w("woocommerce-product-upsell", "bd-rail", columns="4", columns_mobile="2", show_heading=""),
        cls="bd-section bd-reveal", tag="section", gap=24, pad=SECTION_PAD, pad_mobile=pad(0, 16),
    )
    related = con(
        heading("Może Ci się spodobać", "h2", "bd-h2"),
        w("woocommerce-product-related", "bd-rail", columns="4", columns_mobile="2", posts_per_page=8, show_heading=""),
        cls="bd-section bd-reveal", tag="section", gap=24, pad=pad(0, 24, 120, 24), pad_mobile=pad(0, 16, 64, 16),
    )
    return template("bydopamina — Karta produktu", "product", [top, upsell, related])


# ---------------------------------------------------------------------------
# ARCHIWUM PRODUKTÓW (sklep, kategorie, tagi, wyszukiwanie)
# ---------------------------------------------------------------------------

def archive():
    head = con(
        w("woocommerce-breadcrumb", ""),
        w("theme-archive-title", "bd-h2", header_size="h1"),
        w("woocommerce-archive-description", "bd-muted"),
        shortcode("[bd_category_chips]"),
        tag="section", gap=12, pad=pad(32, 24, 24, 24), pad_mobile=pad(16, 16, 16, 16),
    )
    grid = con(
        w("woocommerce-archive-products", "", columns="4", columns_tablet="3", columns_mobile="2", rows="6",
          paginate="yes", allow_order="yes", show_result_count="yes",
          nothing_found_message="Nic tu nie ma – spróbuj innej frazy albo zobacz bestsellery."),
        tag="section", pad=pad(0, 24, 120, 24), pad_mobile=pad(0, 16, 64, 16),
    )
    return template("bydopamina — Sklep / kategoria", "product-archive", [head, grid])


# ---------------------------------------------------------------------------
# KOSZYK, CHECKOUT, KONTO
# ---------------------------------------------------------------------------

STEPS = ('<ol class="bd-steps" aria-label="Kroki zamówienia">'
         '<li{c}>Koszyk</li><li{d}>Dane i dostawa</li><li>Płatność</li></ol>')


def cart():
    return template("bydopamina — Koszyk", "page", [
        con(
            html(STEPS.format(c=' aria-current="step"', d="")),
            heading("Twój koszyk", "h1", "bd-h2"),
            shortcode("[bd_free_shipping_bar]"),
            w("woocommerce-cart", "", layout="two-column", update_cart_automatically="yes", sticky_right_column="yes",
              sticky_right_column_offset=size(96), apply_coupon_heading="Masz kod rabatowy?"),
            shortcode("[bd_trust_badges]"),
            tag="section", gap=24, pad=pad(32, 24, 120, 24), pad_mobile=pad(16, 16, 64, 16),
        )
    ], FULL_PAGE)


def checkout():
    return template("bydopamina — Zamówienie", "page", [
        con(
            html(STEPS.format(c="", d=' aria-current="step"')),
            heading("Zamówienie", "h1", "bd-h2"),
            w("woocommerce-checkout-page", "bd-checkout", layout="two-column", sticky_right_column="yes",
              sticky_right_column_offset=size(96)),
            html('<p class="bd-muted" style="font-size:var(--bd-fs-xs);text-align:center">🔒 Płatności obsługuje licencjonowany operator. '
                 "Nie przechowujemy danych Twojej karty.</p>"),
            tag="section", gap=24, pad=pad(32, 24, 120, 24), pad_mobile=pad(16, 16, 64, 16),
        )
    ], FULL_PAGE)


def account():
    return template("bydopamina — Moje konto", "page", [
        con(
            heading("Moje konto", "h1", "bd-h2"),
            w("woocommerce-my-account", "", tabs_layout="vertical"),
            tag="section", gap=24, pad=pad(32, 24, 120, 24), pad_mobile=pad(16, 16, 64, 16),
        )
    ], FULL_PAGE)


# ---------------------------------------------------------------------------
# KONTAKT, 404
# ---------------------------------------------------------------------------

def contact():
    form = w(
        "form", "",
        form_name="Kontakt",
        form_fields=[
            {"_id": _id(), "custom_id": "name", "field_type": "text", "field_label": "Imię", "required": "true", "width": "50"},
            {"_id": _id(), "custom_id": "email", "field_type": "email", "field_label": "E-mail", "required": "true", "width": "50"},
            {"_id": _id(), "custom_id": "order", "field_type": "text", "field_label": "Numer zamówienia (opcjonalnie)", "width": "100"},
            {"_id": _id(), "custom_id": "message", "field_type": "textarea", "field_label": "Wiadomość", "required": "true", "rows": 5, "width": "100"},
            {"_id": _id(), "custom_id": "hp", "field_type": "honeypot", "width": "100"},
            {"_id": _id(), "custom_id": "rodo", "field_type": "acceptance", "required": "true", "width": "100",
             "acceptance_text": 'Administratorem danych jest bydopamina.pl. Dane przetwarzamy, aby odpowiedzieć na wiadomość. <a href="/polityka-prywatnosci/">Więcej</a>.'},
        ],
        show_labels="yes", button_text="Wyślij wiadomość", button_width="100",
        submit_actions=["email", "save-to-database"],
        email_to="hej@bydopamina.pl", email_subject="Wiadomość ze strony bydopamina.pl",
        success_message="Dziękujemy! Odpowiadamy w ciągu 1 dnia roboczego.",
    )
    return template("bydopamina — Kontakt", "page", [
        con(
            con(
                html('<span class="bd-eyebrow">Kontakt</span>'),
                heading("Napisz do nas – odpisujemy szybko.", "h1", "bd-h2"),
                icon_list([("hej@bydopamina.pl", "mailto:hej@bydopamina.pl"), ("+48 000 000 000", "tel:+48000000000"),
                           ("pn–pt 9:00–16:00", ""), ("Adres do zwrotów: [uzupełnij]", "")]),
                inner=True, boxed=False, width=40, width_tablet=100, gap=20,
            ),
            con(form, inner=True, boxed=False, width=60, width_tablet=100, cls="bd-panel"),
            direction="row", wrap="wrap", gap=48, tag="section", pad=pad(48, 24, 120, 24), pad_mobile=pad(24, 16, 64, 16),
        )
    ], FULL_PAGE)


def error404():
    return template("bydopamina — 404", "error-404", [
        con(
            html('<span class="bd-eyebrow">Błąd 404</span>'),
            heading("Ups, tej strony tu nie ma.", "h1", "bd-hero-title", align="center"),
            text('<p class="bd-muted" style="text-align:center">Może produkt się wyprzedał albo link jest nieaktualny. Spróbuj wyszukać:</p>'),
            w("search-form", "", skin="classic", placeholder="Szukaj produktów…"),
            button("Przejdź do sklepu", "/sklep/", "bd-btn"),
            align="center", gap=24, tag="section", pad=pad(96, 24), pad_mobile=pad(56, 16),
        ),
        con(
            heading("Zamiast tego – bestsellery", "h2", "bd-h3"),
            products_rail("popularity"),
            tag="section", gap=24, pad=pad(0, 24, 120, 24), pad_mobile=pad(0, 16, 64, 16),
        ),
    ])


BUILDERS = {
    "01-header.json": header,
    "02-footer.json": footer,
    "03-strona-glowna.json": home,
    "04-karta-produktu.json": product,
    "05-sklep-archiwum.json": archive,
    "06-koszyk.json": cart,
    "07-zamowienie.json": checkout,
    "08-moje-konto.json": account,
    "09-kontakt.json": contact,
    "10-404.json": error404,
}


def main():
    OUT.mkdir(exist_ok=True)
    for name, fn in BUILDERS.items():
        data = fn()
        (OUT / name).write_text(json.dumps(data, ensure_ascii=False, indent=1) + "\n", encoding="utf-8")
        print(f"✓ {name}  ({data['type']})")


if __name__ == "__main__":
    main()
