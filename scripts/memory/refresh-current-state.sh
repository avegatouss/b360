#!/usr/bin/env bash
# scripts/memory/refresh-current-state.sh
# Rafraîchit docs/memory/CURRENT_STATE.md à partir de l'état réel.

set -euo pipefail

REPO_ROOT="$(git rev-parse --show-toplevel)"
cd "$REPO_ROOT"

# Délégation au bootstrap (idempotent)
bash scripts/memory/bootstrap-from-existing.sh
