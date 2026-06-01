#!/usr/bin/env bash
# scripts/quality/validate-doc-links.sh
# Vérifie que les liens markdown internes pointent vers des fichiers existants.

set -euo pipefail

REPO_ROOT="$(git rev-parse --show-toplevel)"
cd "$REPO_ROOT"

GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m'

BROKEN=0
TOTAL=0

# Cherche tous les liens markdown internes (relatifs)
while IFS= read -r mdfile; do
    while IFS= read -r link; do
        # Extraction du chemin (ignore les ancres et urls)
        target=$(echo "$link" | sed -E 's/.*\]\(([^)#]+)(#[^)]*)?\).*/\1/')
        # Skip URLs externes
        [[ "$target" =~ ^https?:// ]] && continue
        [[ "$target" =~ ^# ]] && continue
        [[ "$target" =~ ^mailto: ]] && continue
        [[ -z "$target" ]] && continue

        # Résoudre relatif au fichier source
        dir=$(dirname "$mdfile")
        full_path="$dir/$target"

        TOTAL=$((TOTAL + 1))

        if [ ! -e "$full_path" ] && [ ! -e "$target" ]; then
            echo -e "${RED}[BROKEN]${NC} $mdfile → $target"
            BROKEN=$((BROKEN + 1))
        fi
    done < <(grep -oE '\]\([^)]+\)' "$mdfile" 2>/dev/null || true)
done < <(find docs/ . -maxdepth 2 -name "*.md" -type f 2>/dev/null | grep -v node_modules | grep -v vendor)

echo ""
echo "Liens vérifiés : $TOTAL"
if [ "$BROKEN" -gt 0 ]; then
    echo -e "${RED}✗ $BROKEN lien(s) cassé(s).${NC}"
    exit 1
fi
echo -e "${GREEN}✓ Tous les liens internes pointent vers des fichiers existants.${NC}"
