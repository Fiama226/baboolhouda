#!/usr/bin/env bash
# =============================================================================
# scripts/test-local.sh — everything that can run without a Docker daemon:
# PHP parse lint (JS), unit tests, data validation, release integrity.
# =============================================================================
set -euo pipefail
cd "$(dirname "$0")/.."
npm test
