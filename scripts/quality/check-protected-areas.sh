#!/usr/bin/env bash
# scripts/quality/check-protected-areas.sh
# Vérifie si la branche courante touche à des zones protégées.
# --staged-only : ne regarde que les fichiers stagés (pour pre-commit).

set -euo pipefail

REPO_ROOT="$(git rev-parse --show-toplevel)"
cd "$REPO_ROOT"

GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

STAGED_ONLY=0
[ "${1:-}" = "--staged-only" ] && STAGED_ONLY=1

# Extraction des patterns L1 et L2 depuis PROTECTED_AREAS.md (si présent)
# Sinon liste hardcodée
L1_PATTERNS=(
    "Modules/Auth"
    "Modules/Core/Database/Traits/BelongsToInstance"
    "Modules/Core/Services/InstanceManager"
    "Modules/Core/Services/InstanceResolver"
    "Modules/Core/Http/Middleware/InstanceMiddleware"
    "Modules/Core/Http/Middleware/BindInstanceFromRoute"
    "Modules/Core/Http/Middleware/EnsureInstanceResolved"
    "Modules/Core/Http/Middleware/SetSpatieTeamContextFromInstance"
    "Modules/Core/Models/AuditLog"
    "Modules/Core/Services/AuditLogger"
    "Modules/Users/Services/Role"
    "Modules/Eshop360/Services/StockService"
    "Modules/Eshop360/Services/StockMovementService"
    "Modules/Eshop360/Services/Pricing"
    "Modules/Eshop360/Services/CashRegisterService"
    "Modules/Billing/Services/PaymentService"
    "Modules/Billing/Services/SubscriptionManager"
    "Modules/Billing/Webhooks"
    "Modules/Eshop360/Services/Payment"
    "app/Instances"
    "app/Models/User.php"
)

L2_PATTERNS=(
    "Modules/Eshop360/Services/InvoiceNumberGenerator"
    "Modules/Eshop360/Services/WalletService"
    "Modules/Eshop360/Services/CommissionService"
    "Modules/ModuleManager"
    "Modules/Billing/Services/FeatureRegistry"
    "Modules/Eshop360/Models/DistributionChannel"
)

# Récupération des fichiers à analyser
if [ "$STAGED_ONLY" -eq 1 ]; then
    CHANGED=$(git diff --cached --name-only --diff-filter=ACMR)
else
    BASE="develop"
    git rev-parse --verify "origin/$BASE" >/dev/null 2>&1 || BASE="main"
    CHANGED=$(git diff --name-only "origin/$BASE...HEAD" 2>/dev/null || git diff --name-only)
fi

[ -z "$CHANGED" ] && exit 0

# Analyse
TOUCHED_L1=()
TOUCHED_L2=()

for pattern in "${L1_PATTERNS[@]}"; do
    if echo "$CHANGED" | grep -q "$pattern"; then
        TOUCHED_L1+=("$pattern")
    fi
done

for pattern in "${L2_PATTERNS[@]}"; do
    if echo "$CHANGED" | grep -q "$pattern"; then
        TOUCHED_L2+=("$pattern")
    fi
done

# Sortie
EXIT_CODE=0
if [ ${#TOUCHED_L1[@]} -gt 0 ]; then
    echo -e "${RED}╔════════════════════════════════════════════════════════════════╗${NC}"
    echo -e "${RED}║  ⚠ ZONES L1 (CRITIQUES) TOUCHÉES                              ║${NC}"
    echo -e "${RED}╚════════════════════════════════════════════════════════════════╝${NC}"
    for area in "${TOUCHED_L1[@]}"; do
        echo -e "  ${RED}•${NC} $area"
    done
    echo ""
    echo -e "${YELLOW}Procédure obligatoire :${NC}"
    echo "  1. IMPACT_ANALYSIS dans la PR (snippet b360-impact)"
    echo "  2. Tests étendus (concurrence + multi-tenant + permissions + idempotence)"
    echo "  3. Double review : Claude Code + Codex"
    echo "  4. ADR si modification structurelle (docs/adr/)"
    echo "  5. Mise à jour : CURRENT_STATE.md, RECENT_DECISIONS.md, CHANGELOG_ARCHITECTURAL.md"
    EXIT_CODE=2
fi

if [ ${#TOUCHED_L2[@]} -gt 0 ]; then
    echo ""
    echo -e "${YELLOW}╔════════════════════════════════════════════════════════════════╗${NC}"
    echo -e "${YELLOW}║  ⚠ ZONES L2 (SENSIBLES) TOUCHÉES                              ║${NC}"
    echo -e "${YELLOW}╚════════════════════════════════════════════════════════════════╝${NC}"
    for area in "${TOUCHED_L2[@]}"; do
        echo -e "  ${YELLOW}•${NC} $area"
    done
    echo ""
    echo "Procédure recommandée :"
    echo "  1. IMPACT_ANALYSIS dans la PR"
    echo "  2. Tests ciblés étendus"
    echo "  3. Review humaine"
    [ "$EXIT_CODE" -eq 0 ] && EXIT_CODE=1
fi

if [ ${#TOUCHED_L1[@]} -eq 0 ] && [ ${#TOUCHED_L2[@]} -eq 0 ]; then
    echo -e "${GREEN}[protected-areas]${NC} Aucune zone protégée touchée."
fi

exit $EXIT_CODE
