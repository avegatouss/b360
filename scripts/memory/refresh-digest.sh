#!/usr/bin/env bash
# scripts/memory/refresh-digest.sh
# Régénère docs/context/PROJECT_DIGEST.md (compression IA).
# Délégué au bootstrap qui sait construire un digest cohérent.

set -euo pipefail

REPO_ROOT="$(git rev-parse --show-toplevel)"
cd "$REPO_ROOT"
bash scripts/memory/bootstrap-from-existing.sh
