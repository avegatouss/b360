#!/usr/bin/env bash
# scripts/ci/verify-setup.sh
# Vérifie que toute la gouvernance du pack est en place.

set -euo pipefail

REPO_ROOT="$(git rev-parse --show-toplevel)"
cd "$REPO_ROOT"

GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

OK=0
KO=0
WARN=0

ok()   { echo -e "${GREEN}[OK]${NC} $1"; OK=$((OK + 1)); }
warn() { echo -e "${YELLOW}[~~]${NC} $1"; WARN=$((WARN + 1)); }
ko()   { echo -e "${RED}[KO]${NC} $1"; KO=$((KO + 1)); }

echo "════════════════════════════════════════════════════════════════"
echo " Vérification setup B360 vibecoding pack"
echo "════════════════════════════════════════════════════════════════"
echo ""

# Fichiers de gouvernance racine
echo "── Fichiers de gouvernance racine ──"
for f in CLAUDE.md CODEX.md AGENTS.md CONTRIBUTING.md ARCHITECTURE.md PROJECT_STATUS.md CHANGELOG_ARCHITECTURAL.md Makefile README_PACK.md INSTALL.md; do
    [ -f "$f" ] && ok "$f" || ko "$f manquant"
done

echo ""
echo "── VSCode ──"
for f in .vscode/settings.json .vscode/tasks.json .vscode/launch.json .vscode/extensions.json .vscode/php.code-snippets .vscode/README.md; do
    [ -f "$f" ] && ok "$f" || warn "$f manquant"
done

echo ""
echo "── Hooks Git ──"
HOOKS_DIR=".git/hooks"
for hook in commit-msg pre-commit pre-push post-commit prepare-commit-msg; do
    if [ -x "$HOOKS_DIR/$hook" ]; then
        ok "Hook installé : $hook"
    else
        ko "Hook manquant ou non exécutable : $hook"
    fi
done

echo ""
echo "── Mémoire projet ──"
for f in docs/memory/CURRENT_STATE.md docs/memory/OPEN_RISKS.md docs/memory/RECENT_DECISIONS.md docs/context/PROJECT_DIGEST.md; do
    if [ -f "$f" ]; then
        ok "$f"
    else
        warn "$f absent (lance : make bootstrap-memory)"
    fi
done

echo ""
echo "── Architecture & gouvernance ──"
for f in docs/architecture/MODULE_DEPENDENCY_MAP.md docs/governance/PROTECTED_AREAS.md docs/index/MODULE_INDEX.md; do
    [ -f "$f" ] && ok "$f" || warn "$f absent"
done

echo ""
echo "── Outils qualité ──"
for f in tools/deptrac/deptrac.yaml tools/phpstan/phpstan.neon tools/phpstan/Rules/NoDirectCrossModuleTableAccess.php tools/rector/rector.php; do
    [ -f "$f" ] && ok "$f" || warn "$f absent"
done

if [ -x "vendor/bin/pint" ]; then ok "vendor/bin/pint exécutable"; else warn "Pint absent (composer require --dev laravel/pint)"; fi
if [ -x "vendor/bin/phpstan" ]; then ok "vendor/bin/phpstan exécutable"; else warn "PHPStan absent (composer require --dev larastan/larastan)"; fi
if [ -x "vendor/bin/deptrac" ]; then ok "vendor/bin/deptrac exécutable"; else warn "Deptrac absent (composer require --dev qossmic/deptrac-shim)"; fi
if [ -x "vendor/bin/pest" ] || [ -x "vendor/bin/phpunit" ]; then ok "Pest/PHPUnit exécutable"; else warn "Pest/PHPUnit absent"; fi

echo ""
echo "── Templates de prompts ──"
for f in templates/prompts/00-bootstrap-claude.md templates/prompts/00-bootstrap-codex.md templates/prompts/01-claude-cadrage.md templates/prompts/02-codex-implementation.md templates/prompts/03-claude-review.md templates/prompts/04-codex-correction.md templates/prompts/05-new-module.md templates/prompts/impact-analysis.md; do
    [ -f "$f" ] && ok "$f" || warn "$f absent"
done

echo ""
echo "── Workflows GitHub ──"
for f in .github/PULL_REQUEST_TEMPLATE.md .github/workflows/ci.yml .github/workflows/protected-areas.yml .github/workflows/architecture-graph.yml; do
    [ -f "$f" ] && ok "$f" || warn "$f absent"
done

echo ""
echo "── ADR ──"
[ -f "docs/adr/_TEMPLATE.md" ] && ok "ADR template" || warn "ADR template absent"
[ -f "docs/adr/ADR-001-pipeline-qualite-local.md" ] && ok "ADR-001 (pipeline qualité)" || warn "ADR-001 absent"

echo ""
echo "── Commitlint ──"
if command -v commitlint >/dev/null 2>&1; then
    ok "commitlint installé"
else
    warn "commitlint non installé globalement (optionnel : npm install -g @commitlint/cli @commitlint/config-conventional)"
fi

echo ""
echo "════════════════════════════════════════════════════════════════"
echo " Bilan : $OK OK / $WARN warnings / $KO erreurs"
echo "════════════════════════════════════════════════════════════════"

if [ "$KO" -gt 0 ]; then
    echo -e "${RED}✗ Setup incomplet — corrige les erreurs ci-dessus.${NC}"
    exit 1
fi

if [ "$WARN" -gt 0 ]; then
    echo -e "${YELLOW}~ Setup fonctionnel mais incomplet — corrige les warnings quand possible.${NC}"
fi

echo -e "${GREEN}✓ Setup OK. Tu peux commencer à coder.${NC}"
echo ""
echo "Prochaines étapes recommandées :"
echo "  1. Lis : CLAUDE.md, CODEX.md, AGENTS.md, CONTRIBUTING.md"
echo "  2. Lance Claude Code : make claude-bootstrap"
echo "  3. Premier lot pilote : unifier BelongsToInstance (R-104)"
exit 0
