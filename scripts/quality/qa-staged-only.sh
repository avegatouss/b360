#!/usr/bin/env bash
# scripts/quality/qa-staged-only.sh
# Pipeline qualité sur fichiers stagés uniquement (utilisé par pre-commit).

set -euo pipefail

REPO_ROOT="$(git rev-parse --show-toplevel)"
cd "$REPO_ROOT"

GREEN='\033[0;32m'
RED='\033[0;31m'
NC='\033[0m'

STAGED=$(git diff --cached --name-only --diff-filter=ACMR | grep -E '\.php$' || true)

if [ -z "$STAGED" ]; then
    echo "Aucun fichier PHP stagé."
    exit 0
fi

echo -e "${GREEN}>>>${NC} Pint sur $(echo "$STAGED" | wc -l | tr -d ' ') fichier(s)..."
echo "$STAGED" | xargs vendor/bin/pint --test || { echo -e "${RED}Pint a échoué.${NC}"; exit 1; }

echo -e "${GREEN}>>>${NC} PHPStan..."
echo "$STAGED" | xargs vendor/bin/phpstan analyse --memory-limit=1G --no-progress || { echo -e "${RED}PHPStan a échoué.${NC}"; exit 1; }

echo -e "${GREEN}OK${NC}"
