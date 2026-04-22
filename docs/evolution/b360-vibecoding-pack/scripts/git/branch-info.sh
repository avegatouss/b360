#!/usr/bin/env bash
# scripts/git/branch-info.sh
# Affiche les infos utiles sur la branche courante : fichiers, modules, zones protégées.

set -euo pipefail

REPO_ROOT="$(git rev-parse --show-toplevel)"
cd "$REPO_ROOT"

GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
RED='\033[0;31m'
NC='\033[0m'

CURRENT=$(git rev-parse --abbrev-ref HEAD)
BASE="develop"
git rev-parse --verify "origin/$BASE" >/dev/null 2>&1 || BASE="main"

echo -e "${BLUE}═══════════════════════════════════════════════════════════════════${NC}"
echo -e "${BLUE} BRANCH INFO — $CURRENT (base: $BASE)${NC}"
echo -e "${BLUE}═══════════════════════════════════════════════════════════════════${NC}"
echo ""

# Commits ahead/behind
AHEAD=$(git rev-list --count "origin/$BASE..HEAD" 2>/dev/null || echo "0")
BEHIND=$(git rev-list --count "HEAD..origin/$BASE" 2>/dev/null || echo "0")
echo -e "${GREEN}Position vs origin/$BASE :${NC} $AHEAD commits ahead, $BEHIND commits behind"
echo ""

# Fichiers modifiés
CHANGED=$(git diff --name-only "origin/$BASE...HEAD" 2>/dev/null || true)
N_CHANGED=$(echo "$CHANGED" | grep -c '.' || echo "0")
echo -e "${GREEN}Fichiers modifiés ($N_CHANGED) :${NC}"
echo "$CHANGED" | head -20 | sed 's/^/  /'
[ "$N_CHANGED" -gt 20 ] && echo "  ... ($((N_CHANGED - 20)) de plus)"
echo ""

# Modules touchés
MODULES=$(echo "$CHANGED" | grep -oE '^Modules/[^/]+' | sort -u | sed 's|Modules/||' || true)
if [ -n "$MODULES" ]; then
    echo -e "${GREEN}Modules touchés :${NC}"
    echo "$MODULES" | sed 's/^/  • /'
else
    echo -e "${YELLOW}Aucun module touché.${NC}"
fi
echo ""

# Zones protégées
PROTECTED_PATTERNS=(
    "Modules/Auth"
    "Modules/Core/Database/Traits/BelongsToInstance"
    "Modules/Core/Services/InstanceManager"
    "Modules/Core/Services/InstanceResolver"
    "Modules/Users/Services/Role"
    "Modules/Eshop360/Services/StockService"
    "Modules/Eshop360/Services/Pricing"
    "Modules/Eshop360/Services/CashRegister"
    "Modules/Billing/Services"
    "Modules/Billing/Webhooks"
    "Modules/Core/Models/AuditLog"
    "app/Models/User.php"
    "app/Instances"
)

TOUCHED_PROTECTED=()
for pattern in "${PROTECTED_PATTERNS[@]}"; do
    if echo "$CHANGED" | grep -q "$pattern"; then
        TOUCHED_PROTECTED+=("$pattern")
    fi
done

if [ ${#TOUCHED_PROTECTED[@]} -gt 0 ]; then
    echo -e "${RED}⚠ Zones protégées touchées :${NC}"
    for area in "${TOUCHED_PROTECTED[@]}"; do
        echo -e "  ${RED}•${NC} $area"
    done
    echo ""
    echo -e "${YELLOW}→ Vérifie : docs/governance/PROTECTED_AREAS.md${NC}"
    echo -e "${YELLOW}→ IMPACT_ANALYSIS obligatoire avant merge${NC}"
fi
echo ""

# Tests à lancer
if [ -n "$MODULES" ]; then
    echo -e "${GREEN}Tests à lancer :${NC}"
    echo "$MODULES" | while read -r m; do
        if [ -d "Modules/$m/Tests" ]; then
            echo "  php artisan test Modules/$m/Tests --parallel"
        fi
    done
fi

# Migrations
MIGRATIONS=$(echo "$CHANGED" | grep -E 'Database/Migrations/|database/migrations/' || true)
if [ -n "$MIGRATIONS" ]; then
    echo ""
    echo -e "${GREEN}Migrations modifiées :${NC}"
    echo "$MIGRATIONS" | sed 's/^/  /'
    echo ""
    echo -e "${YELLOW}→ Pense à : make audit-db${NC}"
fi

# Permissions
PERMS=$(echo "$CHANGED" | grep -iE 'permission|policy|spatie' || true)
if [ -n "$PERMS" ]; then
    echo ""
    echo -e "${GREEN}Permissions/policies touchées :${NC}"
    echo "$PERMS" | sed 's/^/  /'
    echo ""
    echo -e "${YELLOW}→ Pense à : make audit-permissions${NC}"
fi

echo ""
echo -e "${BLUE}═══════════════════════════════════════════════════════════════════${NC}"
