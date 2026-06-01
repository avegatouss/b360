#!/usr/bin/env bash
# scripts/memory/check-freshness.sh
# Vérifie que la mémoire projet est à jour vs HEAD.
# Mode --strict : exit 1 si pas à jour.

set -euo pipefail

REPO_ROOT="$(git rev-parse --show-toplevel)"
cd "$REPO_ROOT"

STRICT=0
[ "${1:-}" = "--strict" ] && STRICT=1

GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

# Fichiers de mémoire critiques
MEMORY_FILES=(
    "docs/memory/CURRENT_STATE.md"
    "docs/memory/OPEN_RISKS.md"
    "docs/memory/RECENT_DECISIONS.md"
    "docs/context/PROJECT_DIGEST.md"
)

# Date du dernier commit applicatif (excluant docs/memory et docs/index)
LAST_CODE_COMMIT=$(git log -1 --format=%ct -- \
    ':(exclude)docs/memory/*' \
    ':(exclude)docs/index/*' \
    ':(exclude)docs/context/PROJECT_DIGEST.md' \
    ':(exclude)CHANGELOG_ARCHITECTURAL.md' \
    Modules/ app/ database/ 2>/dev/null || echo "0")

# Date du dernier commit sur les fichiers mémoire
LAST_MEMORY_COMMIT=$(git log -1 --format=%ct -- "${MEMORY_FILES[@]}" 2>/dev/null || echo "0")

if [ "$LAST_CODE_COMMIT" -eq 0 ]; then
    echo -e "${GREEN}[freshness]${NC} Pas de commits applicatifs détectés."
    exit 0
fi

if [ "$LAST_MEMORY_COMMIT" -eq 0 ]; then
    echo -e "${YELLOW}[freshness]${NC} Aucun fichier de mémoire détecté."
    [ "$STRICT" -eq 1 ] && exit 1
    exit 0
fi

DIFF_DAYS=$(( (LAST_CODE_COMMIT - LAST_MEMORY_COMMIT) / 86400 ))

if [ "$LAST_MEMORY_COMMIT" -lt "$LAST_CODE_COMMIT" ]; then
    if [ "$DIFF_DAYS" -ge 1 ]; then
        echo -e "${YELLOW}[freshness]${NC} Mémoire en retard de $DIFF_DAYS jour(s)."
    else
        echo -e "${YELLOW}[freshness]${NC} Mémoire en retard de quelques heures."
    fi
    echo "Régénère avec : make memory-refresh"

    if [ "$STRICT" -eq 1 ] && [ "$DIFF_DAYS" -ge 2 ]; then
        echo -e "${RED}✗ Mode strict : mémoire en retard de plus de 2 jours.${NC}"
        exit 1
    fi
else
    echo -e "${GREEN}[freshness]${NC} Mémoire à jour."
fi

exit 0
