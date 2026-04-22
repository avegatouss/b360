#!/usr/bin/env bash
# scripts/quality/test-changed-modules.sh
# Lance les tests des modules touchés par la branche courante.

set -euo pipefail

REPO_ROOT="$(git rev-parse --show-toplevel)"
cd "$REPO_ROOT"

GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

BASE="develop"
git rev-parse --verify "origin/$BASE" >/dev/null 2>&1 || BASE="main"

CHANGED=$(git diff --name-only "origin/$BASE...HEAD" 2>/dev/null || true)
MODULES=$(echo "$CHANGED" | grep -oE '^Modules/[^/]+' | sort -u | sed 's|Modules/||' || true)

if [ -z "$MODULES" ]; then
    echo -e "${YELLOW}Aucun module touché.${NC}"
    exit 0
fi

echo -e "${GREEN}>>>${NC} Modules à tester : $(echo "$MODULES" | tr '\n' ' ')"

EXIT=0
for module in $MODULES; do
    if [ -d "Modules/$module/Tests" ]; then
        echo -e "${GREEN}>>>${NC} Tests Modules/$module/Tests..."
        if ! php artisan test "Modules/$module/Tests" --parallel; then
            echo -e "${RED}✗ Tests $module échoués.${NC}"
            EXIT=1
        fi
    else
        echo -e "${YELLOW}~~~${NC} Modules/$module : pas de Tests/ — skip"
    fi
done

exit $EXIT
