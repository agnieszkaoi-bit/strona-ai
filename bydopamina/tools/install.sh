#!/usr/bin/env bash
# Instalacja sklepu bydopamina przez WP-CLI (hosting z SSH).
# Użycie (w katalogu WordPressa):  bash install.sh <folder-z-dist> <ścieżka-do-elementor-pro.zip>
set -euo pipefail

DIST="${1:?Podaj folder z plikami bydopamina-child.zip i bydopamina-setup.zip}"
PRO="${2:-}"

wp core is-installed || { echo "Najpierw zainstaluj WordPress (w panelu hostingu)."; exit 1; }

wp language core install pl_PL --activate || true
wp theme install hello-elementor
wp theme install "$DIST/bydopamina-child.zip" --activate
wp plugin install woocommerce elementor --activate
if [ -n "$PRO" ] && [ -f "$PRO" ]; then
	wp plugin install "$PRO" --activate
else
	echo "! Pominięto Elementor Pro – wgraj go w panelu i aktywuj licencję, potem uruchom: wp bydopamina setup"
fi

# Zalecane darmowe wtyczki (włącz i skonfiguruj po instalacji).
wp plugin install ti-woocommerce-wishlist woo-variation-swatches two-factor updraftplus complianz-gdpr --activate || true

wp plugin install "$DIST/bydopamina-setup.zip" --activate
wp language plugin install woocommerce elementor pl_PL || true
wp bydopamina check
if wp plugin is-active elementor-pro; then
	wp bydopamina setup
fi
echo "Gotowe. Dalsze kroki: INSTALACJA.md → Krok 4."
