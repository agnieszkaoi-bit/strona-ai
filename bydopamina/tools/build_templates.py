#!/usr/bin/env python3
"""
Generuje szablony Elementora (JSON) dla bydopamina.pl do katalogu elementor-templates/.

Uruchom:  python3 tools/build_templates.py
Import:   WordPress → Szablony → Kreator motywu / Zapisane szablony → „Importuj szablony”.

Kierunek: editorial jewelry 2026/27 – asymetryczna siatka, szeryf Instrument Serif + Geist/Geist Mono,
numerowane nagłówki sekcji, kafelki kategorii, nastroje, „shop the look”, prawdziwe opinie, rozmiarówka.
Wygląd pochodzi z klas bd-* motywu potomnego, więc szablony są lekkie, a marka żyje w tokens.css.
"""
import json
import random
from pathlib import Path

OUT = Path(__file__).resolve().parent.parent / "elementor-templates"
_rng = random.Random(2027)  # deterministyczne ID → czytelne diffy w git


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
    """Kontener flex. Skróty: justify, align, wrap, gap, width(+_tablet/_mobile), pad(+_mobile), bg, minh."""
    settings = {"content_width": "boxed" if boxed else "full", "flex_direction": direction}
    if boxed:
        settings["boxed_width"] = size(1520)
    if cls:
        settings["css_classes"] = cls
    if tag:
        settings["html_tag"] = tag
    mapping = {"justify": "flex_justify_content", "align": "flex_align_items", "wrap": "flex_wrap",
               "direction_tablet": "flex_direction_tablet", "direction_mobile": "flex_direction_mobile"}
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
        else:
            settings[k] = v
    return {"id": _id(), "elType": "container", "isInner": inner, "settings": settings, "elements": list(children)}


def box(*children, **s):
    """Wewnętrzny kontener pełnej szerokości (kolumna/grupa)."""
    s.setdefault("boxed", False)
    return con(*children, inner=True, **s)


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


def html(markup, cls=""):
    return w("html", cls, html=markup)


def shortcode(code, cls=""):
    return w("shortcode", cls, shortcode=code)


def button(label, url, cls="bd-btn"):
    return w("button", cls, text=label, link={"url": url, "is_external": "", "nofollow": ""}, size="md")


def textlink(label, url):
    return html(f'<a class="bd-textlink" href="{url}">{label} <span aria-hidden="true">→</span></a>')


def image(cls="", alt=""):
    # Pusty URL = placeholder Elementora (podmień w edytorze). Brak zewnętrznych pobrań przy imporcie.
    return w("image", cls, image={"url": "", "id": "", "alt": alt}, image_size="full")


def icon_list(items, cls="", inline=False):
    s = {"view": "inline" if inline else "traditional",
         "icon_list": [{"_id": _id(), "text": t, "link": {"url": u, "is_external": "", "nofollow": ""},
                        "selected_icon": {"value": "", "library": ""}} for t, u in items]}
    return w("icon-list", cls, **s)


def sechead(title, sub=None, link_label=None, url=None):
    """Nagłówek sekcji: tytuł (+ krótki opis) po lewej, link po prawej."""
    left = [heading(title, "h2", "bd-h2")]
    if sub:
        left.append(text(f"<p>{sub}</p>"))
    parts = [box(*left, gap=0)]
    if link_label:
        parts.append(textlink(link_label, url))
    return box(*parts, direction="row", cls="bd-sechead", align="flex-end", wrap="wrap")


def template(title, kind, content, page_settings=None):
    return {"version": "0.4", "title": title, "type": kind, "page_settings": page_settings or {}, "content": content}


FULL_PAGE = {"template": "elementor_header_footer", "hide_title": "yes"}
SIDE = pad(0, 40, 0, 40)
SIDE_M = pad(0, 16, 0, 16)


def section(*children, cls="bd-section", **s):
    s.setdefault("pad", pad(0, 40, 0, 40))
    s.setdefault("pad_mobile", SIDE_M)
    return con(*children, cls=cls, tag="section", **s)


# ---------------------------------------------------------------------------
# HEADER
# ---------------------------------------------------------------------------

def header():
    """Układ jak w klasycznych sklepach jubilerskich: pasek promocji → pasek informacyjny → logo | menu | szukaj + ikony z podpisami."""
    promobar = con(
        html('<a href="/sklep/?on_sale=1">Druga sztuka −30% · tylko do niedzieli &gt;&gt;&gt;</a>'),
        boxed=False, cls="bd-promobar", pad=pad(0),
    )
    utilbar = con(
        html('<ul><li>Darmowa dostawa od 199 zł</li><li>30 dni na zwrot</li><li>Pudełko prezentowe gratis</li></ul>'),
        html('<ul class="bd-utilbar__links"><li><a href="/kontakt/">Kontakt i pomoc</a></li><li><a href="/karta-podarunkowa/">Karty podarunkowe</a></li></ul>'),
        cls="bd-utilbar", direction="row", justify="space-between", align="center", wrap="wrap",
        pad=pad(0, 40), hide_mobile="hidden-mobile",
    )
    logo = heading("bydopamina<b>.</b>", "div", "bd-logo", link="/")
    # Menu: Nowości · Biżuteria · Bestsellery · Na prezent · Promocje (pozycji promocyjnej nadaj klasę CSS bd-menu-promo w Wygląd → Menu).
    nav = w("nav-menu", "", menu="", layout="horizontal", align_items="center", pointer="underline",
            animation_line="fade", dropdown="tablet", toggle="burger", full_width="stretch", text_align="aside",
            submenu_icon={"value": "", "library": ""})

    def icon_box(icon, title, url, cls=""):
        return w("icon-box", cls, selected_icon={"value": icon, "library": "fa-regular"}, title_text=title,
                 description_text="", position="top", title_size="span",
                 link={"url": url, "is_external": "", "nofollow": ""})

    icons = box(
        w("search-form", "bd-header-search", skin="minimal", placeholder="Szukaj",
          icon="search", size={"unit": "px", "size": 40}),
        icon_box("far fa-user", "Profil", "/moje-konto/", "bd-hide-mobile"),
        icon_box("far fa-heart", "Ulubione", "/lista-zyczen/", "bd-hide-mobile"),
        w("woocommerce-menu-cart", "", icon="bag-light", items_indicator="bubble", hide_empty_indicator="yes",
          cart_type="side-cart", open_cart="click", automatically_open_cart="yes", show_subtotal="no"),
        direction="row", align="center", cls="bd-header-icons", gap=6,
    )
    bar = con(
        logo, nav, icons,
        boxed=False, cls="bd-header", tag="header", minh=84, pad=pad(0, 40), pad_mobile=pad(0, 8),
        sticky="top", sticky_on=["desktop", "tablet", "mobile"], sticky_effects_offset=size(40), z_index=100,
    )
    return template("bydopamina — Header", "header", [promobar, utilbar, bar])


# ---------------------------------------------------------------------------
# FOOTER
# ---------------------------------------------------------------------------

def footer():
    cols = box(
        box(
            heading("Biżuteria w kolorach, które poprawiają humor. Stal, ceramika, perły i muszle – na co dzień.", "p", "bd-h3"),
            w("social-icons", "", shape="square", social_icon_list=[
                {"_id": _id(), "social_icon": {"value": "fab fa-instagram", "library": "fa-brands"}, "link": {"url": "https://instagram.com/bydopamina", "is_external": "on", "nofollow": ""}},
                {"_id": _id(), "social_icon": {"value": "fab fa-tiktok", "library": "fa-brands"}, "link": {"url": "https://tiktok.com/@bydopamina", "is_external": "on", "nofollow": ""}},
                {"_id": _id(), "social_icon": {"value": "fab fa-pinterest", "library": "fa-brands"}, "link": {"url": "https://pinterest.com/bydopamina", "is_external": "on", "nofollow": ""}},
            ], icon_color="custom", icon_primary_color="#00000000", icon_secondary_color="#F6F2EC"),
            width=40, width_tablet=100, gap=24,
        ),
        box(heading("Sklep", "h2", "bd-footer-h"), icon_list([
            ("Naszyjniki", "/kategoria-produktu/naszyjniki/"), ("Kolczyki", "/kategoria-produktu/kolczyki/"),
            ("Pierścionki", "/kategoria-produktu/pierscionki/"), ("Bransoletki", "/kategoria-produktu/bransoletki/"),
            ("Karta podarunkowa", "/karta-podarunkowa/")]), width=20, width_tablet=33, width_mobile=50, gap=16),
        box(heading("Pomoc", "h2", "bd-footer-h"), icon_list([
            ("Dostawa i płatności", "/dostawa-i-platnosci/"), ("Zwroty i reklamacje", "/zwroty/"),
            ("Rozmiarówka", "/rozmiarowka/"), ("Pielęgnacja biżuterii", "/pielegnacja/"), ("Kontakt", "/kontakt/")]),
            width=20, width_tablet=33, width_mobile=50, gap=16),
        box(heading("Kontakt", "h2", "bd-footer-h"), icon_list([
            ("hej@bydopamina.pl", "mailto:hej@bydopamina.pl"), ("+48 000 000 000", "tel:+48000000000"),
            ("pn–pt 9:00–16:00", "")]), width=20, width_tablet=33, width_mobile=100, gap=16),
        direction="row", wrap="wrap", gap=40,
    )
    payments = html('<ul class="bd-payments" aria-label="Płatności i dostawa"><li>BLIK</li><li>Visa</li><li>Mastercard</li>'
                    '<li>Apple Pay</li><li>Google Pay</li><li>Przelewy24</li><li>PayPo</li><li>InPost</li><li>DPD</li></ul>')
    wordmark = html('<p class="bd-footer-wordmark" aria-hidden="true">bydopamina<b>.</b></p>')
    bottom = box(
        shortcode("© [bd_year] bydopamina.pl", "bd-label"),
        icon_list([("Regulamin", "/regulamin/"), ("Polityka prywatności", "/polityka-prywatnosci/"),
                   ("Cookies", "/polityka-cookies/"), ("Odstąpienie od umowy", "/odstapienie-od-umowy/"),
                   ("Deklaracja dostępności", "/deklaracja-dostepnosci/")], inline=True),
        direction="row", justify="space-between", align="center", wrap="wrap", gap=16, direction_mobile="column",
    )
    main = con(cols, payments, wordmark, bottom, cls="bd-footer bd-dark", tag="footer", bg="#1C1917", gap=48,
               pad=pad(96, 40, 32, 40), pad_mobile=pad(64, 16, 24, 16))
    mobile_nav = con(shortcode("[bd_mobile_nav]"), boxed=False, pad=pad(0),
                     hide_desktop="hidden-desktop", hide_tablet="hidden-tablet")
    return template("bydopamina — Footer", "footer", [main, mobile_nav])


# ---------------------------------------------------------------------------
# STRONA GŁÓWNA
# ---------------------------------------------------------------------------

def home():
    """Prosta ścieżka: hero → zaufanie → kategorie → bestsellery → nastroje (wyróżnik) → stylizacja → prezent → opinie → newsletter."""
    # Hero = slider na całą szerokość (Elementor Pro „Slides”). Podmień zdjęcia tła (min. 2400×1300 px, WebP).
    hero = con(
        w("slides", "bd-hero-slider",
          slides=[
            {"_id": _id(), "heading": "Kolor na co dzień", "description": "Nowa kolekcja", "button_text": "Sprawdź",
             "link": {"url": "/sklep/?orderby=date", "is_external": "", "nofollow": ""}, "link_click": "button",
             "background_color": "#B98F7A", "background_image": {"url": "", "id": ""}, "background_size": "cover",
             "background_overlay": "yes", "background_overlay_color": "rgba(20,14,12,0.35)",
             "horizontal_position": "left", "vertical_position": "middle", "text_align": "left"},
            {"_id": _id(), "heading": "Prezent, który cieszy", "description": "Pakowanie gratis", "button_text": "Wybierz prezent",
             "link": {"url": "/kategoria-produktu/prezenty/", "is_external": "", "nofollow": ""}, "link_click": "button",
             "background_color": "#9C6B74", "background_image": {"url": "", "id": ""}, "background_size": "cover",
             "background_overlay": "yes", "background_overlay_color": "rgba(20,14,12,0.35)",
             "horizontal_position": "left", "vertical_position": "middle", "text_align": "left"},
            {"_id": _id(), "heading": "Bestsellery", "description": "Pokochały je klientki", "button_text": "Zobacz",
             "link": {"url": "/sklep/?orderby=popularity", "is_external": "", "nofollow": ""}, "link_click": "button",
             "background_color": "#7D8F8B", "background_image": {"url": "", "id": ""}, "background_size": "cover",
             "background_overlay": "yes", "background_overlay_color": "rgba(20,14,12,0.35)",
             "horizontal_position": "left", "vertical_position": "middle", "text_align": "left"},
          ],
          slides_height={"unit": "vh", "size": 78}, slides_height_mobile={"unit": "vh", "size": 70},
          navigation="both", autoplay="yes", autoplay_speed=6000, pause_on_hover="yes", infinite="yes",
          transition="fade", transition_speed=600, content_max_width={"unit": "%", "size": 60},
          content_max_width_mobile={"unit": "%", "size": 100}),
        boxed=False, cls="bd-hero", pad=pad(0),
    )

    def carousel_section(title, link_label, url, sc, **kw):
        return section(sechead(title, None, link_label, url), shortcode(sc), cls="bd-section bd-reveal", **kw)

    bestsellers = carousel_section("Bestsellery", "Zobacz wszystko ›", "/sklep/?orderby=popularity",
                                   '[bd_products_carousel type="bestsellers" limit="10"]')

    categories = section(
        heading("Biżuteria", "h2", "bd-h2"),
        # Kafle = kategorie główne (miniatura kategorii 4:5). Kolejność: Produkty → Kategorie.
        shortcode('[bd_category_arches limit="4"]'),
        cls="bd-section bd-reveal", gap=28, pad=pad(0, 40), pad_mobile=SIDE_M,
    )

    new_in = carousel_section("Nowości", "Zobacz wszystko ›", "/sklep/?orderby=date",
                              '[bd_products_carousel type="new" limit="10"]')

    moods = section(
        sechead("Biżuteria na nastrój", None, None, None),
        # Wyróżnik marki. Opcjonalnie zdjęcia: [bd_mood_picker images="radosc:123,spokoj:456"]
        shortcode("[bd_mood_picker]"),
        cls="bd-section bd-reveal", pad=pad(0, 40), pad_mobile=SIDE_M,
    )

    sets = carousel_section("Zestawy biżuterii", "Zobacz wszystko ›", "/kategoria-produktu/zestawy/",
                            '[bd_products_carousel type="all" category="zestawy" limit="10"]')

    look = section(
        sechead("Noś razem", None, "Więcej stylizacji ›", "/stylizacje/"),
        # Podmień: image = ID zdjęcia z Mediów, products = ID:x%:y% (pozycja kropki na zdjęciu).
        shortcode('[bd_shop_the_look image="0" products="101:34:38,102:52:30,103:61:66" title="Na zdjęciu"]'),
        cls="bd-section bd-reveal", pad=pad(0, 40), pad_mobile=SIDE_M,
    )

    def banner(title, url):
        return con(
            html(f'<span class="bd-banner__text"><strong>{title}</strong><span>Pokaż więcej ›</span></span>'),
            inner=True, boxed=False, cls="bd-banner", html_tag="a", link={"url": url, "is_external": "", "nofollow": ""},
            background_background="classic", background_image={"url": "", "id": ""}, background_size="cover",
            background_position="center center", minh=180,
        )

    banners = section(
        box(banner("Na prezent", "/kategoria-produktu/prezenty/"), banner("Karty podarunkowe", "/karta-podarunkowa/"),
            cls="bd-banners", direction="row"),
        cls="bd-section", pad=pad(0, 40), pad_mobile=SIDE_M,
    )

    reviews = section(
        sechead("Opinie klientek", None, "Wszystkie opinie ›", "/opinie/"),
        shortcode("[bd_rating_summary]"),
        shortcode('[bd_reviews limit="10"]'),
        cls="bd-section bd-reveal", gap=24, pad=pad(0, 40), pad_mobile=SIDE_M,
    )

    newsletter_form = w(
        "form", "bd-newsletter",
        form_name="Newsletter",
        form_fields=[
            {"_id": _id(), "custom_id": "email", "field_type": "email", "field_label": "Adres e-mail",
             "placeholder": "Podaj adres e-mail*", "required": "true", "width": "100"},
            {"_id": _id(), "custom_id": "hp", "field_type": "honeypot", "width": "100"},
            {"_id": _id(), "custom_id": "zgoda", "field_type": "acceptance", "required": "true", "width": "100",
             "acceptance_text": 'Chcę zapisać się do newslettera. Zapoznałam/em się z <a href="/polityka-prywatnosci/">Polityką prywatności</a>.'},
        ],
        show_labels="", button_text="Dołącz", button_width="100",
        submit_actions=["save-to-database"],
        success_message="Gotowe! Potwierdź zapis w mailu – kod rabatowy wyślemy od razu.",
        error_message="Coś poszło nie tak. Spróbuj ponownie.",
    )
    newsletter = section(
        con(
            box(image("", "Biżuteria na stole"), cls="bd-nl-split__img"),
            box(heading("Odbierz 10% rabatu na pierwsze zakupy", "h2", ""),
                text("<p>Dołącz do newslettera – nowe kolekcje, stylizacje i promocje zobaczysz przed wszystkimi.</p>"),
                newsletter_form, cls="bd-nl-split__panel"),
            inner=True, boxed=False, cls="bd-nl-split", direction="row",
        ),
        cls="bd-section", pad=pad(0, 0, 0, 0),
    )

    return template("bydopamina — Strona główna", "page",
                    [hero, bestsellers, categories, new_in, moods, sets, look, banners, reviews, newsletter], FULL_PAGE)


# ---------------------------------------------------------------------------
# KARTA PRODUKTU
# ---------------------------------------------------------------------------

def nested_accordion(items):
    return {
        "id": _id(), "elType": "widget", "widgetType": "nested-accordion", "isInner": False,
        "settings": {
            "items": [{"_id": _id(), "item_title": t} for t, _ in items],
            "default_state": "all_collapsed",
            "max_items_expended": "one",
            "_css_classes": "bd-accordion",
        },
        "elements": [box(*ws, pad=pad(0, 0, 20, 0)) for _, ws in items],
    }


CARE = ("<p>Stal 316L ze złoceniem PVD nie boi się wody ani potu. Żeby złoto błyszczało latami:</p>"
        "<ul><li>perfumy i balsam nakładaj przed założeniem biżuterii,</li>"
        "<li>czyść miękką ściereczką, bez past i środków ściernych,</li>"
        "<li>przechowuj osobno – w pudełku lub woreczku, żeby elementy się nie rysowały.</li></ul>")
GPSR = ("<p><strong>Producent:</strong> [nazwa, adres, e-mail]<br><strong>Podmiot odpowiedzialny w UE:</strong> [jeśli inny]<br>"
        "<strong>Ostrzeżenia:</strong> zawiera małe elementy – nie dla dzieci poniżej 3 lat.</p>")
SHIPPING = ("<p><strong>InPost Paczkomat</strong> i <strong>kurier DPD</strong> – 1–2 dni robocze. Darmowa dostawa od 199 zł.</p>"
            "<p>30 dni na zwrot bez podawania przyczyny. <a href=\"/zwroty/\">Jak zwrócić produkt</a></p>")


def product():
    gallery = box(w("woocommerce-product-images", "bd-pdp-gallery", sale_flash="yes"), width=58, width_tablet=100)
    summary = box(
        w("woocommerce-breadcrumb", "bd-label"),
        shortcode("[bd_product_claims]"),
        w("woocommerce-product-title", "bd-pdp-title", header_size="h1"),
        w("woocommerce-product-rating", ""),
        w("woocommerce-product-price", "bd-pdp-price"),
        w("woocommerce-product-short-description", "bd-muted"),
        shortcode("[bd_size_guide]"),
        w("woocommerce-product-add-to-cart", "", show_quantity="yes", layout="stacked"),
        shortcode("[bd_complete_set]"),
        shortcode("[bd_delivery_eta]"),
        shortcode("[bd_free_shipping_bar]"),
        shortcode('[bd_usp variant="list"]'),
        nested_accordion([
            ("Opis", [w("woocommerce-product-content", "")]),
            ("Materiał i pielęgnacja", [text(CARE)]),
            ("Wymiary i szczegóły", [w("woocommerce-product-additional-information", "", show_heading="")]),
            ("Dostawa i zwroty", [text(SHIPPING)]),
            ("Bezpieczeństwo produktu", [text(GPSR)]),
            ("Opinie", [shortcode("[bd_product_reviews]")]),
        ]),
        width=42, width_tablet=100, gap=16, cls="bd-sticky-col", pad=pad(0, 0, 0, 24), pad_tablet=pad(0),
    )
    top = con(gallery, summary, direction="row", wrap="wrap", gap=32, tag="section",
              pad=pad(24, 40, 96, 40), pad_mobile=pad(0, 0, 48, 0))
    related = section(
        sechead("Może Ci się spodobać"),
        w("woocommerce-product-related", "bd-rail", columns="4", columns_mobile="2", posts_per_page=8, show_heading=""),
        cls="bd-section bd-reveal", pad=pad(0, 40, 120, 40), pad_mobile=pad(0, 16, 72, 16),
    )
    return template("bydopamina — Karta produktu", "product", [top, related])


# ---------------------------------------------------------------------------
# ARCHIWUM PRODUKTÓW
# ---------------------------------------------------------------------------

def archive():
    head = section(
        w("woocommerce-breadcrumb", "bd-label"),
        box(
            w("theme-archive-title", "bd-h2", header_size="h1"),
            w("woocommerce-archive-description", "bd-lead"),
            direction="row", justify="space-between", align="flex-end", wrap="wrap", gap=24,
        ),
        shortcode("[bd_category_chips]"),
        shortcode('[bd_shop_by]'),
        cls="bd-archive-head", gap=20, pad=pad(40, 40, 32, 40), pad_mobile=pad(20, 16, 20, 16),
    )
    grid = section(
        w("woocommerce-archive-products", "", columns="4", columns_tablet="3", columns_mobile="2", rows="12",
          paginate="yes", allow_order="yes", show_result_count="yes",
          nothing_found_message="Nic tu nie ma. Spróbuj innej frazy albo zajrzyj do bestsellerów."),
        cls="bd-section", pad=pad(0, 40, 120, 40), pad_mobile=pad(0, 16, 72, 16),
    )
    return template("bydopamina — Sklep / kategoria", "product-archive", [head, grid])


# ---------------------------------------------------------------------------
# KOSZYK, CHECKOUT, KONTO, KONTAKT, 404
# ---------------------------------------------------------------------------

def steps(current):
    names = ["Koszyk", "Dane i dostawa", "Płatność"]
    cur = ' aria-current="step"'
    li = "".join(f'<li{cur if i == current else ""}>{n}</li>' for i, n in enumerate(names))
    return html(f'<ol class="bd-steps" aria-label="Kroki zamówienia">{li}</ol>')


def cart():
    return template("bydopamina — Koszyk", "page", [section(
        steps(0),
        heading("Koszyk", "h1", "bd-h2"),
        shortcode("[bd_free_shipping_bar]"),
        w("woocommerce-cart", "", layout="two-column", update_cart_automatically="yes", sticky_right_column="yes",
          sticky_right_column_offset=size(96)),
        shortcode("[bd_trust_badges]"),
        cls="bd-section", gap=28, pad=pad(40, 40, 120, 40), pad_mobile=pad(20, 16, 72, 16),
    )], FULL_PAGE)


def checkout():
    return template("bydopamina — Zamówienie", "page", [section(
        steps(1),
        heading("Zamówienie", "h1", "bd-h2"),
        w("woocommerce-checkout-page", "bd-checkout", layout="two-column", sticky_right_column="yes",
          sticky_right_column_offset=size(96)),
        html('<p class="bd-label" style="text-align:center">Płatności obsługuje licencjonowany operator. Nie przechowujemy danych Twojej karty.</p>'),
        cls="bd-section", gap=28, pad=pad(40, 40, 120, 40), pad_mobile=pad(20, 16, 72, 16),
    )], FULL_PAGE)


def account():
    return template("bydopamina — Moje konto", "page", [section(
        heading("Moje konto", "h1", "bd-h2"),
        w("woocommerce-my-account", "", tabs_layout="vertical"),
        cls="bd-section", gap=28, pad=pad(40, 40, 120, 40), pad_mobile=pad(20, 16, 72, 16),
    )], FULL_PAGE)


def contact():
    form = w(
        "form", "",
        form_name="Kontakt",
        form_fields=[
            {"_id": _id(), "custom_id": "name", "field_type": "text", "field_label": "Imię", "required": "true", "width": "50"},
            {"_id": _id(), "custom_id": "email", "field_type": "email", "field_label": "E-mail", "required": "true", "width": "50"},
            {"_id": _id(), "custom_id": "order", "field_type": "text", "field_label": "Numer zamówienia (jeśli dotyczy)", "width": "100"},
            {"_id": _id(), "custom_id": "message", "field_type": "textarea", "field_label": "Wiadomość", "required": "true", "rows": 5, "width": "100"},
            {"_id": _id(), "custom_id": "hp", "field_type": "honeypot", "width": "100"},
            {"_id": _id(), "custom_id": "rodo", "field_type": "acceptance", "required": "true", "width": "100",
             "acceptance_text": 'Administratorem danych jest bydopamina.pl. Dane przetwarzamy, aby odpowiedzieć na wiadomość. <a href="/polityka-prywatnosci/">Więcej</a>.'},
        ],
        show_labels="yes", button_text="Wyślij", button_width="100",
        submit_actions=["email", "save-to-database"],
        email_to="hej@bydopamina.pl", email_subject="Wiadomość ze strony bydopamina.pl",
        success_message="Dziękujemy. Odpowiadamy w ciągu 1 dnia roboczego.",
    )
    return template("bydopamina — Kontakt", "page", [section(
        box(
            box(heading("Kontakt", "h1", "bd-h2"), text("<p>Odpisujemy w ciągu 1 dnia roboczego.</p>", "bd-lead"),
                icon_list([("hej@bydopamina.pl", "mailto:hej@bydopamina.pl"), ("+48 000 000 000", "tel:+48000000000"),
                           ("pn–pt 9:00–16:00", ""), ("Adres do zwrotów: [uzupełnij]", "")]),
                width=40, width_tablet=100, gap=24),
            box(form, width=60, width_tablet=100, cls="bd-panel"),
            direction="row", wrap="wrap", gap=48,
        ),
        cls="bd-section", pad=pad(48, 40, 120, 40), pad_mobile=pad(24, 16, 72, 16),
    )], FULL_PAGE)


def error404():
    return template("bydopamina — 404", "error-404", [
        section(
            html('<p class="bd-label">Błąd 404</p>'),
            heading("Nie znaleźliśmy tej strony", "h1", "bd-h2"),
            text("<p>Produkt mógł się wyprzedać albo link jest nieaktualny. Spróbuj wyszukać:</p>", "bd-lead"),
            w("search-form", "", skin="classic", placeholder="Szukaj biżuterii…"),
            button("Przejdź do sklepu", "/sklep/"),
            cls="bd-section", gap=24, pad=pad(96, 40, 64, 40), pad_mobile=pad(48, 16, 48, 16),
        ),
        section(sechead("Zamiast tego – bestsellery"), shortcode('[bd_product_tabs limit="4"]'),
                cls="bd-section", pad=pad(0, 40, 120, 40), pad_mobile=pad(0, 16, 72, 16)),
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
