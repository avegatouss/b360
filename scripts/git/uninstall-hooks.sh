#!/usr/bin/env bash
# scripts/git/uninstall-hooks.sh
# Désinstalle les hooks Git du pack vibecoding B360.

set -euo pipefail

REPO_ROOT="$(git rev-parse --show-toplevel)"
HOOKS_DIR="$REPO_ROOT/.git/hooks"

GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

for hook in commit-msg pre-commit pre-push post-commit prepare-commit-msg; do
    if [ -f "$HOOKS_DIR/$hook" ]; then
        # Restaurer le backup le plus récent s'il existe
        latest_backup=$(ls -t "$HOOKS_DIR/$hook.backup-"* 2>/dev/null | head -n 1 || true)
        if [ -n "$latest_backup" ]; then
            mv "$latest_backup" "$HOOKS_DIR/$hook"
            echo -e "${GREEN}[uninstall]${NC} Restauré : $hook (depuis $latest_backup)"
        else
            rm "$HOOKS_DIR/$hook"
            echo -e "${GREEN}[uninstall]${NC} Supprimé : $hook"
        fi
    fi
done

echo -e "${GREEN}[uninstall]${NC} Hooks désinstallés."
