#!/usr/bin/env bash
# Buduje paczki do wgrania w WordPressie:
#   dist/bydopamina-child.zip  – motyw (Wygląd → Motywy → Dodaj → Wyślij motyw)
#   dist/bydopamina-setup.zip  – kreator konfiguracji (Wtyczki → Dodaj nową → Wyślij wtyczkę)
set -euo pipefail
cd "$(dirname "$0")/.."
python3 tools/build_templates.py >/dev/null
rm -rf dist build && mkdir -p dist build
cp -r theme/bydopamina-child build/bydopamina-child
cp -r plugin/bydopamina-setup build/bydopamina-setup
mkdir -p build/bydopamina-setup/templates
cp elementor-templates/*.json build/bydopamina-setup/templates/
(cd build && zip -qr -X ../dist/bydopamina-child.zip bydopamina-child && zip -qr -X ../dist/bydopamina-setup.zip bydopamina-setup)
rm -rf build
ls -la dist
