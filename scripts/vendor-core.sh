#!/usr/bin/env bash
# =============================================================================
# scripts/vendor-core.sh
# Fetches the pinned WordPress core (sources.json) into site/ so the tree is
# a complete, deployable WordPress root. Core is git-ignored.
# =============================================================================
set -euo pipefail
cd "$(dirname "$0")/.."

if [ ! -f site/wp-settings.php ]; then
    ref="$(node -e 'console.log(JSON.parse(require("fs").readFileSync("sources.json","utf8")).wordpress_core.ref)' 2>/dev/null \
        || python3 -c 'import json;print(json.load(open("sources.json"))["wordpress_core"]["ref"])')"
    echo ">> Vendoring WordPress core @ ${ref} into site/"
    tmp="$(mktemp -d)"
    curl -fsSL "https://github.com/WordPress/WordPress/archive/refs/tags/${ref}.tar.gz" -o "${tmp}/wp.tgz"
    tar -xzf "${tmp}/wp.tgz" -C site --strip-components=1
    rm -rf "${tmp}"
    echo ">> Done."
else
    echo ">> WordPress core already vendored in site/ (skipped)."
fi
