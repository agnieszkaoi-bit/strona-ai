#!/usr/bin/env python3
"""
Generuje szablony Elementora (JSON) dla bydopamina.pl do katalogu elementor-templates/.

Uruchom:  python3 tools/build_templates.py
Import:   WordPress → Szablony → Kreator motywu / Zapisane szablony → „Importuj szablony”.

Kierunek: editorial jewelry 2026/27 – asymetryczna siatka, szeryf Instrument Serif + Geist/Geist Mono,
numerowane nagłówki sekcji, łuki kategorii, „shop the look”, prawdziwe opinie, rozmiarówka.
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


def sechead(num, title, link_label=None, url=None, dark=False):
    """Nagłówek sekcji w układzie redakcyjnym: 01 · Tytuł · link."""
    parts = [html(f'<p class="bd-label">{num}</p>'), heading(title, "h2", "bd-h2")]
    parts.append(textlink(link_label, url) if link_label else html(""))
    return box(*parts, direction="row", cls="bd-sechead", align="flex-end")


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
    announcement = con(
        html('<ul><li>Darmowa dostawa od 199 zł</li><li>Wysyłka w 24 h</li><li>30 dni na zwrot</li><li>Pudełko prezentowe w cenie</li></ul>'),
        boxed=False, cls="bd-announcement", pad=pad(0),
    )
    nav = w("nav-menu", "", menu="", layout="horizontal", align_items="left", pointer="underline",
            animation_line="fade", dropdown="tablet", toggle="burger", full_width="stretch", text_align="aside",
            submenu_icon={"value": "", "library": ""})
    logo = heading("bydopamina", "div", "bd-logo", link="/")
    icons = box(
        w("search-form", "", skin="full_screen", placeholder="Szukaj: naszyjnik, obrączka, kolczyki…"),
        w("icon", "bd-hide-mobile", selected_icon={"value": "far fa-heart", "library": "fa-regular"},
          link={"url": "/lista-zyczen/", "is_external": "", "nofollow": ""}, _title="Ulubione"),
        w("icon", "bd-hide-mobile", selected_icon={"value": "far fa-user", "library": "fa-regular"},
          link={"url": "/moje-konto/", "is_external": "", "nofollow": ""}, _title="Moje konto"),
        w("woocommerce-menu-cart", "", icon="bag-light", items_indicator="bubble", hide_empty_indicator="yes",
          cart_type="side-cart", open_cart="click", automatically_open_cart="yes", show_subtotal="no"),
        direction="row", align="center", cls="bd-header-icons", gap=0,
    )
    bar = con(
        nav, logo, icons,
        boxed=False, cls="bd-header", tag="header", minh=64,
        pad=pad(0, 40), pad_mobile=pad(0, 8),
        sticky="top", sticky_on=["desktop", "tablet", "mobile"], sticky_effects_offset=size(40), z_index=100,
    )
    return template("bydopamina — Header", "header", [announcement, bar])


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
    wordmark = html('<p class="bd-footer-wordmark" aria-hidden="true">bydopamina</p>')
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

SPECS = """<ul class="bd-specs">
<li><b>316L</b><span>Stal chirurgiczna – ta sama, z której robi się implanty. Nie rdzewieje, nie zmienia koloru.</span></li>
<li><b>18K</b><span>Złoto nakładane metodą PVD – warstwa wiązana z metalem, a nie tylko nim pokryta.</span></li>
<li><b>0</b><span>Niklu i ołowiu. Bezpieczna dla wrażliwej skóry i do noszenia 24/7.</span></li>
</ul>"""

HERO_META = """<dl class="bd-meta">
<dt>Materiały</dt><dd>Stal 316L, złoto 18K, ceramika, perły</dd>
<dt>Wysyłka</dt><dd>W 24 h, InPost lub kurier</dd>
<dt>Zwrot</dt><dd>30 dni, bez podawania przyczyny</dd>
</dl>"""


def home():
    hero = section(
        box(
            heading("Kolor, który <em>robi dzień</em>.", "h1", "bd-hero-title bd-hero__title"),
            box(
                image("bd-lcp", "Modelka w naszyjnikach z kolekcji"),
                html('<div class="bd-caption"><span class="bd-label">Kolekcja 07 — Lagoon</span>'
                     '<a class="bd-textlink" href="/kolekcja/lagoon/">Zobacz kolekcję <span aria-hidden="true">→</span></a></div>'),
                # Kolor tła = kolor kolekcji: zmień bd-tint--lagoon na coral / lilac / lime / sun / rose.
                cls="bd-hero__main bd-media--hero bd-tint bd-tint--lagoon",
            ),
            box(
                image("bd-media bd-media--sq", "Zbliżenie: pierścionki na dłoni"),
                text("<p>Stal, ceramika, perły i muszle w kolorach, które poprawiają humor. "
                     "Nowa kolekcja co sezon, noszona na co dzień.</p>", "bd-lead"),
                box(button("Kup kolekcję", "/kolekcja/lagoon/"), textlink("Bestsellery", "/sklep/?orderby=popularity"),
                    direction="row", align="center", wrap="wrap", gap=24),
                html(HERO_META),
                cls="bd-hero__side",
            ),
            cls="bd-hero__grid",
        ),
        cls="bd-hero", pad=pad(24, 40, 0, 40), pad_mobile=pad(12, 16, 0, 16),
    )

    usp = section(shortcode("[bd_usp]"), cls="bd-usp", pad=pad(48, 40), pad_mobile=pad(40, 16))

    categories = section(
        sechead("01", "Kategorie", "Cały sklep", "/sklep/"),
        shortcode("[bd_category_arches limit=\"6\"]"),
        cls="bd-section bd-reveal", pad=pad(64, 40, 0, 40), pad_mobile=pad(40, 16, 0, 16),
    )

    colours = section(
        sechead("02", "Szukaj po <em>kolorze</em>, kamieniu, materiale", "Cały sklep", "/sklep/"),
        # Zakładki Kolor / Kamień / Materiał z atrybutów pa_kolor, pa_kamien, pa_material (puste zakładki się ukrywają).
        shortcode("[bd_shop_by]"),
        cls="bd-section bd-reveal", pad=pad(64, 40, 0, 40), pad_mobile=pad(48, 16, 0, 16),
    )

    products = section(
        box(html('<p class="bd-label">03 — Wybór redakcji</p>'), textlink("Wszystkie produkty", "/sklep/"),
            direction="row", justify="space-between", align="center", pad=pad(0, 0, 16, 0)),
        shortcode('[bd_product_tabs limit="8"]'),
        cls="bd-section bd-reveal",
    )

    material = section(
        sechead("04", "Stal, która <em>nie ciemnieje</em>. Nawet pod prysznicem.", "O materiale", "/o-materiale/", dark=True),
        box(
            box(text("<p>Tanie złocenie to kilka mikronów farby na mosiądzu – ściera się po paru tygodniach i zostawia zielony ślad. "
                     "Nasze złoto jest związane ze stalą próżniowo. Dlatego kolor zostaje z Tobą na lata.</p>", "bd-lead"),
                html(SPECS), width=58, width_tablet=100, cls="bd-material-col"),
            box(image("bd-unveil", "Biżuteria w wodzie – zbliżenie"), cls="bd-media bd-media--45", width=42, width_tablet=100),
            direction="row", wrap="wrap", gap=48, align="flex-end",
        ),
        cls="bd-section bd-dark", bg="#1C1917",
    )

    look = section(
        sechead("05", "Stylizacja <em>tygodnia</em>", "Więcej stylizacji", "/stylizacje/"),
        # Podmień: image = ID zdjęcia z Mediów, products = ID:x%:y% (pozycja kropki na zdjęciu).
        shortcode('[bd_shop_the_look image="0" products="101:34:38,102:52:30,103:61:66" title="Na zdjęciu"]'),
        cls="bd-section bd-reveal",
    )

    def collection(title, count, url, alt, tint):
        return box(
            image("bd-unveil", alt),
            html(f'<div class="bd-collection__meta"><h3>{title}</h3><span class="bd-label">{count}</span></div>'),
            textlink("Odkryj", url),
            cls=f"bd-collection bd-media--45 bd-tint bd-tint--{tint}",
        )

    collections = section(
        sechead("06", "Kolekcje <em>sezonu</em>", "Wszystkie kolekcje", "/kolekcje/"),
        box(collection("Coral", "18 modeli", "/kolekcja/coral/", "Koralowe kolczyki z ceramiki", "coral"),
            collection("Lagoon", "24 modele", "/kolekcja/lagoon/", "Turkusowy naszyjnik z muszlą", "lagoon"),
            collection("Lilac", "15 modeli", "/kolekcja/lilac/", "Liliowa bransoletka z perłą", "lilac"),
            cls="bd-collections"),
        cls="bd-section bd-reveal", pad=pad(0, 40), pad_mobile=SIDE_M,
    )

    gifts = section(
        box(
            box(
                html('<p class="bd-label">07 — Prezenty</p>'),
                heading("Prezent, który <em>nie trafi</em> do szuflady.", "h2", "bd-h2"),
                text("<p>Każde zamówienie pakujemy w pudełko. Chcesz więcej? Zaznacz „pakowanie na prezent” – dołożymy papier, "
                     "wstążkę i liścik z Twoimi słowami. Bez paragonu w paczce.</p>", "bd-lead"),
                shortcode("[bd_gift_finder]"),
                width=55, width_tablet=100, gap=24,
            ),
            box(image("bd-unveil", "Pudełko prezentowe z biżuterią"), cls="bd-media bd-media--45", width=45, width_tablet=100),
            direction="row", wrap="wrap", gap=56, align="center",
        ),
        cls="bd-section bd-sand", bg="#EDE6DC",
    )

    reviews = section(
        sechead("08", "Opinie", "Wszystkie opinie", "/opinie/"),
        shortcode("[bd_rating_summary]"),
        shortcode('[bd_reviews limit="10"]'),
        cls="bd-section bd-reveal", gap=24,
    )

    newsletter_form = w(
        "form", "bd-newsletter",
        form_name="Newsletter",
        form_fields=[
            {"_id": _id(), "custom_id": "email", "field_type": "email", "field_label": "Adres e-mail",
             "placeholder": "twoj@email.pl", "required": "true", "width": "70"},
            {"_id": _id(), "custom_id": "hp", "field_type": "honeypot", "width": "100"},
            {"_id": _id(), "custom_id": "zgoda", "field_type": "acceptance", "required": "true", "width": "100",
             "acceptance_text": 'Chcę dostawać newsletter (maks. 2 maile w miesiącu). <a href="/polityka-prywatnosci/">Polityka prywatności</a>.'},
        ],
        show_labels="yes", button_text="Zapisz się", button_width="30",
        submit_actions=["save-to-database"],
        success_message="Gotowe. Potwierdź zapis w mailu – kod −10% wyślemy od razu.",
        error_message="Coś poszło nie tak. Spróbuj ponownie.",
    )
    newsletter = section(
        box(
            box(heading("Nowe kolekcje <em>najpierw</em> u Ciebie. I −10% na start.", "h2", "bd-h2"),
                width=50, width_tablet=100),
            box(newsletter_form, width=50, width_tablet=100),
            direction="row", wrap="wrap", gap=48, align="flex-end",
            pad=pad(56, 0, 0, 0), cls="bd-newsletter-wrap", border_border="solid",
            border_width={"unit": "px", "top": "1", "right": "0", "bottom": "0", "left": "0", "isLinked": False},
            border_color="#DCD2C4",
        ),
        cls="bd-section", pad=pad(0, 40, 120, 40), pad_mobile=pad(0, 16, 72, 16),
    )

    return template("bydopamina — Strona główna", "page",
                    [hero, usp, categories, colours, products, material, look, collections, gifts, reviews, newsletter], FULL_PAGE)


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
    together = section(
        sechead("01", "Noś <em>razem</em>"),
        w("woocommerce-product-upsell", "bd-rail", columns="4", columns_mobile="2", show_heading=""),
        cls="bd-section bd-reveal", pad=pad(0, 40, 96, 40), pad_mobile=pad(0, 16, 64, 16),
    )
    related = section(
        sechead("02", "Może Ci się <em>spodobać</em>"),
        w("woocommerce-product-related", "bd-rail", columns="4", columns_mobile="2", posts_per_page=8, show_heading=""),
        cls="bd-section bd-reveal", pad=pad(0, 40, 120, 40), pad_mobile=pad(0, 16, 72, 16),
    )
    return template("bydopamina — Karta produktu", "product", [top, together, related])


# ---------------------------------------------------------------------------
# ARCHIWUM PRODUKTÓW
# ---------------------------------------------------------------------------

def archive():
    head = section(
        w("woocommerce-breadcrumb", "bd-label"),
        box(
            w("theme-archive-title", "bd-hero-title", header_size="h1"),
            w("woocommerce-archive-description", "bd-lead"),
            direction="row", justify="space-between", align="flex-end", wrap="wrap", gap=24,
        ),
        shortcode("[bd_category_chips]"),
        shortcode('[bd_shop_by]'),
        cls="bd-archive-head", gap=20, pad=pad(40, 40, 32, 40), pad_mobile=pad(20, 16, 20, 16),
    )
    grid = section(
        w("woocommerce-archive-products", "", columns="4", columns_tablet="3", columns_mobile="2", rows="6",
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
            box(html('<p class="bd-label">Kontakt</p>'), heading("Napisz. <em>Odpisujemy szybko.</em>", "h1", "bd-h2"),
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
            heading("Tej strony <em>tu nie ma</em>.", "h1", "bd-hero-title"),
            text("<p>Produkt mógł się wyprzedać albo link jest nieaktualny. Spróbuj wyszukać:</p>", "bd-lead"),
            w("search-form", "", skin="classic", placeholder="Szukaj biżuterii…"),
            button("Przejdź do sklepu", "/sklep/"),
            cls="bd-section", gap=24, pad=pad(96, 40, 64, 40), pad_mobile=pad(48, 16, 48, 16),
        ),
        section(sechead("—", "Zamiast tego"), shortcode('[bd_product_tabs limit="4"]'),
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
