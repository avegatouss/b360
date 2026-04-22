#!/usr/bin/env bash
# scripts/git/install-hooks.sh
# Installe les hooks Git de gouvernance B360.
# Idempotent : peut être relancé sans risque.

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(git rev-parse --show-toplevel)"
HOOKS_DIR="$REPO_ROOT/.git/hooks"

GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

log()   { echo -e "${GREEN}[install-hooks]${NC} $1"; }
warn()  { echo -e "${YELLOW}[install-hooks]${NC} $1"; }
fatal() { echo -e "${RED}[install-hooks]${NC} $1" >&2; exit 1; }

[ -d "$REPO_ROOT/.git" ] || fatal "Pas un dépôt Git."

mkdir -p "$HOOKS_DIR"

# Sauvegarde des hooks existants si non vides
backup_existing() {
    local hook="$1"
    if [ -f "$HOOKS_DIR/$hook" ] && [ -s "$HOOKS_DIR/$hook" ]; then
        local backup="$HOOKS_DIR/$hook.backup-$(date +%Y%m%d-%H%M%S)"
        cp "$HOOKS_DIR/$hook" "$backup"
        warn "Backup créé : $backup"
    fi
}

install_hook() {
    local hook_name="$1"
    backup_existing "$hook_name"
    cp "$SCRIPT_DIR/hooks/$hook_name" "$HOOKS_DIR/$hook_name"
    chmod +x "$HOOKS_DIR/$hook_name"
    log "Installé : .git/hooks/$hook_name"
}

# Vérifier que les hooks sources existent
for hook in commit-msg pre-commit pre-push post-commit prepare-commit-msg; do
    [ -f "$SCRIPT_DIR/hooks/$hook" ] || fatal "Hook source manquant : scripts/git/hooks/$hook"
done

install_hook commit-msg
install_hook pre-commit
install_hook pre-push
install_hook post-commit
install_hook prepare-commit-msg

# Configurer commitlint si présent
if command -v commitlint >/dev/null 2>&1; then
    log "commitlint détecté."
else
    warn "commitlint non installé globalement. Installation recommandée :"
    warn "  npm install -g @commitlint/cli @commitlint/config-conventional"
fi

# Configurer la longueur max du sujet pour Git
git config commit.template ".gitmessage" 2>/dev/null || true

log "${GREEN}Tous les hooks Git sont installés.${NC}"
log ""
log "Pour désinstaller : bash scripts/git/uninstall-hooks.sh"
log "Pour bypasser un hook (urgence uniquement) : git commit --no-verify"
