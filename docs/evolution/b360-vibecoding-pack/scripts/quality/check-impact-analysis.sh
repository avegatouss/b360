#!/usr/bin/env bash
# scripts/quality/check-impact-analysis.sh
# Vérifie que les commits récents touchant des zones L1 contiennent un IMPACT_ANALYSIS.

set -euo pipefail

REPO_ROOT="$(git rev-parse --show-toplevel)"
cd "$REPO_ROOT"

YELLOW='\033[1;33m'
GREEN='\033[0;32m'
NC='\033[0m'

BASE="develop"
git rev-parse --verify "origin/$BASE" >/dev/null 2>&1 || BASE="main"

# Détecter si zone L1 touchée
if ! bash scripts/quality/check-protected-areas.sh > /tmp/protected-output 2>&1; then
    L1_DETECTED=$(grep -c "ZONES L1" /tmp/protected-output 2>/dev/null || echo "0")
else
    L1_DETECTED=0
fi

if [ "$L1_DETECTED" -eq 0 ]; then
    exit 0
fi

# Chercher IMPACT_ANALYSIS dans les commits récents
COMMITS_BODY=$(git log "origin/$BASE..HEAD" --format=%B 2>/dev/null || echo "")

if echo "$COMMITS_BODY" | grep -qiE 'impact[ _-]?analysis'; then
    echo -e "${GREEN}[OK]${NC} IMPACT_ANALYSIS détecté dans les commits."
    exit 0
fi

# Chercher dans docs/memory récemment modifiés
RECENT_MEMORY=$(git diff --name-only "origin/$BASE...HEAD" -- docs/memory/ docs/adr/ 2>/dev/null || true)
if [ -n "$RECENT_MEMORY" ]; then
    echo -e "${GREEN}[OK]${NC} Documentation de gouvernance mise à jour dans la branche."
    exit 0
fi

echo -e "${YELLOW}[WARN]${NC} Zones L1 touchées mais aucun IMPACT_ANALYSIS détecté dans les commits ni dans docs/memory ou docs/adr."
echo -e "${YELLOW}[WARN]${NC} Recommandé : ajouter un commit docs(governance) avec l'analyse d'impact."
exit 1
