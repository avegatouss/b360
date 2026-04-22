#!/usr/bin/env bash
# scripts/memory/refresh-api-index.sh
# Régénère docs/index/API_INDEX.md à partir de php artisan route:list.

set -euo pipefail

REPO_ROOT="$(git rev-parse --show-toplevel)"
cd "$REPO_ROOT"

GREEN='\033[0;32m'
NC='\033[0m'

OUTPUT="docs/index/API_INDEX.md"
NOW=$(date '+%Y-%m-%d %H:%M:%S %Z')

mkdir -p docs/index

cat > "$OUTPUT" <<EOF
# API_INDEX — B360

> Auto-généré par \`make audit-api\`. Mise à jour : **$NOW**
> Source : \`php artisan route:list\`.

---

## Routes par module

EOF

if command -v php >/dev/null 2>&1 && [ -f artisan ]; then
    if php artisan route:list --json 2>/dev/null > /tmp/routes.json; then
        if command -v jq >/dev/null 2>&1; then
            jq -r '
                group_by(.name | split(".")[0]) |
                .[] |
                "### " + (.[0].name // "unnamed" | split(".")[0]) + "\n\n" +
                (map("- `" + .method + " " + .uri + "`" + (if .name then " → `" + .name + "`" else "" end)) | join("\n")) + "\n\n"
            ' /tmp/routes.json >> "$OUTPUT" 2>/dev/null || cat /tmp/routes.json >> "$OUTPUT"
        else
            echo "(installe \`jq\` pour le formatage propre des routes)" >> "$OUTPUT"
            php artisan route:list --columns=method,uri,name --no-ansi >> "$OUTPUT"
        fi
        rm -f /tmp/routes.json
    else
        echo "(php artisan route:list a échoué — vérifie ton .env / DB)" >> "$OUTPUT"
    fi
else
    echo "(php ou artisan absent)" >> "$OUTPUT"
fi

cat >> "$OUTPUT" <<EOF

---

## Convention

- Préfixe d'instance : \`/i/{slug}/...\`
- Routes API publiques : \`/api/v1/...\`
- Versionning : breaking change → nouvelle route \`/api/v2/...\`
- Toute route mutative est protégée par middleware d'auth + Policy + check feature si applicable
- Toute réponse d'erreur respecte le format normalisé (Laravel ProblemDetails ou format maison documenté)
EOF

echo -e "${GREEN}[OK]${NC} $OUTPUT régénéré."
