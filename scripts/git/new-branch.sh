#!/usr/bin/env bash
# scripts/git/new-branch.sh <type> <scope> <subject>
# Crée une branche normalisée et commence depuis la base à jour.

set -euo pipefail

TYPE="${1:-}"
SCOPE="${2:-}"
SUBJECT="${3:-}"

GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

if [ -z "$TYPE" ] || [ -z "$SCOPE" ] || [ -z "$SUBJECT" ]; then
    echo -e "${RED}Usage : bash scripts/git/new-branch.sh <type> <scope> <subject>${NC}"
    echo ""
    echo "Exemples :"
    echo "  bash scripts/git/new-branch.sh feat pricing channel-engine"
    echo "  bash scripts/git/new-branch.sh fix inventory race-condition-stock"
    echo "  bash scripts/git/new-branch.sh refactor core unify-belongs-to-instance"
    exit 1
fi

# Validation type
VALID_TYPES="feat fix refactor perf test docs chore build ci style security"
if ! echo " $VALID_TYPES " | grep -q " $TYPE "; then
    echo -e "${RED}✗ Type invalide : $TYPE${NC}"
    echo "Types autorisés : $VALID_TYPES"
    exit 1
fi

# Slugify subject
SLUG=$(echo "$SUBJECT" | tr '[:upper:]' '[:lower:]' | sed 's/[^a-z0-9-]/-/g' | sed 's/--*/-/g' | sed 's/^-//;s/-$//')

if [ -z "$SLUG" ]; then
    echo -e "${RED}✗ Subject invalide après slugification.${NC}"
    exit 1
fi

BRANCH="$TYPE/$SCOPE-$SLUG"

# Détection de la branche de base
# Ordre : $B360_BASE_BRANCH (env) → develop → base → main → master
BASE_BRANCH=""
if [ -n "${B360_BASE_BRANCH:-}" ]; then
    BASE_BRANCH="$B360_BASE_BRANCH"
else
    for CANDIDATE in develop base main master; do
        if git rev-parse --verify "$CANDIDATE" >/dev/null 2>&1 \
            || git rev-parse --verify "origin/$CANDIDATE" >/dev/null 2>&1; then
            BASE_BRANCH="$CANDIDATE"
            break
        fi
    done
fi

if [ -z "$BASE_BRANCH" ]; then
    echo -e "${RED}✗ Aucune branche de base détectée (develop, base, main, master).${NC}"
    echo "Définis B360_BASE_BRANCH=<nom> pour forcer."
    exit 1
fi

# Vérifier qu'il n'y a pas de changements en cours
if ! git diff-index --quiet HEAD --; then
    echo -e "${YELLOW}⚠ Tu as des modifications non commitées.${NC}"
    git status --short
    echo ""
    read -p "Continuer (stash automatique) ? (y/N) " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        exit 1
    fi
    git stash push -u -m "auto-stash before $BRANCH"
    STASHED=1
else
    STASHED=0
fi

# Vérifier que la branche n'existe pas déjà
if git rev-parse --verify "$BRANCH" >/dev/null 2>&1; then
    echo -e "${YELLOW}⚠ La branche $BRANCH existe déjà localement.${NC}"
    git checkout "$BRANCH"
    exit 0
fi

# Mise à jour de la base
echo -e "${GREEN}>>>${NC} Mise à jour de $BASE_BRANCH..."
if git rev-parse --verify "origin/$BASE_BRANCH" >/dev/null 2>&1; then
    git fetch origin "$BASE_BRANCH"
    git checkout "$BASE_BRANCH"
    git pull --ff-only origin "$BASE_BRANCH" || echo -e "${YELLOW}⚠ pull non fast-forward — la base locale est en avance, on continue.${NC}"
else
    echo -e "${YELLOW}⚠ origin/$BASE_BRANCH inexistante — utilisation de la branche locale.${NC}"
    git checkout "$BASE_BRANCH"
fi

# Création de la branche
echo -e "${GREEN}>>>${NC} Création de $BRANCH..."
git checkout -b "$BRANCH"

# Restaurer le stash
if [ "$STASHED" -eq 1 ]; then
    git stash pop || echo -e "${YELLOW}⚠ Conflit lors du stash pop. Résous manuellement.${NC}"
fi

echo ""
echo -e "${GREEN}╔════════════════════════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║  ✓ Branche créée : $BRANCH${NC}"
echo -e "${GREEN}╚════════════════════════════════════════════════════════════════╝${NC}"
echo ""
echo "Prochaines étapes recommandées :"
echo "  1. Cadrage avec Claude Code (templates/prompts/01-claude-cadrage.md)"
echo "  2. Implémentation avec Codex (templates/prompts/02-codex-implementation.md)"
echo "  3. Review avec Claude Code (templates/prompts/03-claude-review.md)"
echo "  4. make qa"
echo "  5. git commit (template auto-injecté)"
echo "  6. git push"
