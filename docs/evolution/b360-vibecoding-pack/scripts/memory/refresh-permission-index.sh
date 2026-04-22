#!/usr/bin/env bash
# scripts/memory/refresh-permission-index.sh
# Régénère docs/index/PERMISSION_INDEX.md à partir des HookRegistry et permissions Spatie.

set -euo pipefail

REPO_ROOT="$(git rev-parse --show-toplevel)"
cd "$REPO_ROOT"

GREEN='\033[0;32m'
NC='\033[0m'

OUTPUT="docs/index/PERMISSION_INDEX.md"
NOW=$(date '+%Y-%m-%d %H:%M:%S %Z')

mkdir -p docs/index

cat > "$OUTPUT" <<EOF
# PERMISSION_INDEX — B360

> Auto-généré par \`make audit-permissions\`. Mise à jour : **$NOW**

> Source : extraction des chaînes ressemblant à des permissions Spatie dans les HooksProviders et PermissionGroup.

---

## Permissions détectées par module

EOF

if [ -d "Modules" ]; then
    for dir in Modules/*/; do
        module=$(basename "$dir")
        # Recherche de patterns courants pour les permissions
        perms=$(grep -rhoE "['\"][a-z]+\.[a-z\._-]+['\"]" \
            --include="*HookProvider*.php" \
            --include="*ServiceProvider.php" \
            --include="*PermissionGroup*.php" \
            --include="permissions.php" \
            "$dir" 2>/dev/null \
            | tr -d "'\"" \
            | sort -u || true)

        if [ -n "$perms" ]; then
            echo "### $module" >> "$OUTPUT"
            echo "" >> "$OUTPUT"
            echo "$perms" | while read -r p; do
                echo "- \`$p\`" >> "$OUTPUT"
            done
            echo "" >> "$OUTPUT"
        fi
    done
fi

cat >> "$OUTPUT" <<EOF

## Permissions Spatie en base (si DB accessible)

\`\`\`bash
php artisan tinker --execute="dump(\\Spatie\\Permission\\Models\\Permission::pluck('name'));"
\`\`\`

---

## Convention

- format \`<scope>.<resource>.<action>\` (ex: \`eshop.product.create\`)
- toute permission est exposée via \`HookRegistry\` (groupe \`PermissionGroup\`)
- toute permission est rattachée à un check explicite dans une Policy ou un middleware
EOF

echo -e "${GREEN}[OK]${NC} $OUTPUT régénéré."
