#!/usr/bin/env bash
# =============================================================================
# scripts/build-release.sh
# Builds the cPanel release zip: a complete WordPress root (core + theme +
# plugins + wp-config-sample + .htaccess). Upload & unzip into public_html.
# =============================================================================
set -euo pipefail
cd "$(dirname "$0")/.."

echo ">> 1/3 Rebuilding assets"
npm run build >/dev/null

echo ">> 2/3 Vendoring WordPress core"
bash scripts/vendor-core.sh

echo ">> 3/3 Packaging"
rm -rf release
mkdir -p release
stage="$(mktemp -d)"
cp -R site/. "${stage}/"
rm -f "${stage}/wp-config.php" "${stage}/wp-content/debug.log"
# Remove dev leftovers from the mu-plugins folder if any.
find "${stage}" -name ".DS_Store" -delete
( cd "${stage}" && zip -qr "${OLDPWD}/release/tv-baaboul-houda-release.zip" . )
rm -rf "${stage}"
echo ">> Release zip ready: release/tv-baaboul-houda-release.zip"
du -h release/tv-baaboul-houda-release.zip
