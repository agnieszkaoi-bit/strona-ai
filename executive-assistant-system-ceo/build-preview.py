#!/usr/bin/env python3
"""
Buduje jednoplikowy podgląd strony (podgląd/system-ceo-preview.html).

Wszystko ląduje w jednym pliku HTML: CSS, JS i fonty jako data URI.
Dzięki temu podgląd działa też tam, gdzie zablokowane są zapytania
do zewnętrznych hostów. Źródłem prawdy pozostają index.html, css/ i js/ —
ten skrypt niczego nie modyfikuje, tylko skleja.

Użycie:  python3 build-preview.py
"""

import base64
import pathlib
import re

ROOT = pathlib.Path(__file__).resolve().parent
OUT = ROOT / "podglad" / "system-ceo-preview.html"


def font_to_data_uri(css: str) -> str:
    """Zamienia url(../fonts/x.woff2) na osadzone data URI."""
    def repl(m):
        rel = m.group(1)
        path = (ROOT / "css" / rel).resolve()
        blob = base64.b64encode(path.read_bytes()).decode("ascii")
        return f"url(data:font/woff2;base64,{blob})"

    return re.sub(r"url\((\.\./fonts/[^)]+\.woff2)\)", repl, css)


def main() -> None:
    html = (ROOT / "index.html").read_text(encoding="utf-8")
    fonts = font_to_data_uri((ROOT / "css" / "fonts.css").read_text(encoding="utf-8"))
    style = (ROOT / "css" / "style.css").read_text(encoding="utf-8")
    script = (ROOT / "js" / "script.js").read_text(encoding="utf-8")

    # preload/link do arkuszy -> jeden blok <style>
    html = re.sub(
        r'\s*<link rel="preload"[^>]*>\s*|\s*<link rel="stylesheet" href="css/(?:fonts|style)\.css">\s*',
        "",
        html,
    )
    html = html.replace(
        "</head>",
        "<style>\n" + fonts + "\n" + style + "\n</style>\n</head>",
    )

    # zewnętrzny <script src> -> kod inline
    html = html.replace(
        '<script src="js/script.js"></script>',
        "<script>\n" + script + "\n</script>",
    )

    OUT.parent.mkdir(parents=True, exist_ok=True)
    OUT.write_text(html, encoding="utf-8")
    print(f"{OUT.relative_to(ROOT)} — {OUT.stat().st_size / 1024:.0f} KB")


if __name__ == "__main__":
    main()
