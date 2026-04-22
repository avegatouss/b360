#!/usr/bin/env bash
# scripts/memory/refresh-db-index.sh
# Régénère docs/index/DB_INDEX.md à partir des migrations.

set -euo pipefail

REPO_ROOT="$(git rev-parse --show-toplevel)"
cd "$REPO_ROOT"

GREEN='\033[0;32m'
NC='\033[0m'

OUTPUT="docs/index/DB_INDEX.md"
NOW=$(date '+%Y-%m-%d %H:%M:%S %Z')

mkdir -p docs/index

cat > "$OUTPUT" <<EOF
# DB_INDEX — B360

> Auto-généré par \`make audit-db\`. Mise à jour : **$NOW**

---

## Tables détectées (à partir des migrations)

EOF

# Tables du dossier database/migrations
if [ -d "database/migrations" ]; then
    echo "### Migrations racine (\`database/migrations/\`)" >> "$OUTPUT"
    echo "" >> "$OUTPUT"
    grep -hE "Schema::create\('([^']+)'" database/migrations/*.php 2>/dev/null \
        | sed -E "s/.*Schema::create\('([^']+)'.*/- \`\\1\`/" \
        | sort -u >> "$OUTPUT" || true
    echo "" >> "$OUTPUT"
fi

# Tables par module
if [ -d "Modules" ]; then
    for dir in Modules/*/Database/Migrations/; do
        [ -d "$dir" ] || continue
        module=$(basename "$(dirname "$(dirname "$dir")")")
        tables=$(grep -hE "Schema::create\('([^']+)'" "$dir"*.php 2>/dev/null \
            | sed -E "s/.*Schema::create\('([^']+)'.*/\\1/" \
            | sort -u || true)

        if [ -n "$tables" ]; then
            echo "### $module" >> "$OUTPUT"
            echo "" >> "$OUTPUT"
            echo "$tables" | while read -r t; do
                # Compte les migrations qui modifient cette table
                modifs=$(grep -lE "Schema::table\('$t'|Schema::create\('$t'" "$dir"*.php 2>/dev/null | wc -l | tr -d ' ')
                echo "- \`$t\` ($modifs migration(s))" >> "$OUTPUT"
            done
            echo "" >> "$OUTPUT"
        fi
    done
fi

cat >> "$OUTPUT" <<EOF

## Statut migrations (snapshot)

\`\`\`
$(php artisan migrate:status 2>/dev/null | tail -n +1 | head -50 || echo "(php artisan migrate:status a échoué)")
\`\`\`

---

## Convention

- Préfixe par module (\`eshop_\`, \`billing_\`, \`core_\`, \`auth_\`)
- Toute table métier a une colonne \`instance_id\` (foreign key vers \`instances\`)
- Index obligatoires : \`instance_id\`, colonnes de filtre fréquentes
- Soft delete uniquement si métier (\`deleted_at\`)
- Pas de \`dropColumn\` ni \`renameColumn\` sans plan documenté dans \`docs/runbooks/\`
- Migrations additives uniquement, \`up()\` et \`down()\` réversibles
EOF

echo -e "${GREEN}[OK]${NC} $OUTPUT régénéré."
