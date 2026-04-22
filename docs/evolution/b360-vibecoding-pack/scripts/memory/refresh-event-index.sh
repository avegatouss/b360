#!/usr/bin/env bash
# scripts/memory/refresh-event-index.sh
# Régénère docs/index/EVENT_INDEX.md à partir des classes Events et Listeners.

set -euo pipefail

REPO_ROOT="$(git rev-parse --show-toplevel)"
cd "$REPO_ROOT"

GREEN='\033[0;32m'
NC='\033[0m'

OUTPUT="docs/index/EVENT_INDEX.md"
NOW=$(date '+%Y-%m-%d %H:%M:%S %Z')

mkdir -p docs/index

cat > "$OUTPUT" <<EOF
# EVENT_INDEX — B360

> Auto-généré par \`make audit-events\`. Mise à jour : **$NOW**

---

## Événements publiés (par module)

EOF

if [ -d "Modules" ]; then
    for dir in Modules/*/; do
        module=$(basename "$dir")
        # Classes dans Events/
        events=$(find "$dir/Events" "$dir/Domain/Events" -name "*.php" -type f 2>/dev/null \
            | sed "s|.*/||;s|\.php$||" \
            | sort -u || true)
        # event() ou ::dispatch( pour publication
        dispatched=$(grep -rhoE "(event\(new [A-Z][a-zA-Z0-9_]+|[A-Z][a-zA-Z0-9_]+::dispatch\()" "$dir" 2>/dev/null \
            | sed -E "s/event\\(new ([A-Z][a-zA-Z0-9_]+).*/\\1/;s/([A-Z][a-zA-Z0-9_]+)::dispatch\\(/\\1/" \
            | sort -u || true)

        if [ -n "$events" ] || [ -n "$dispatched" ]; then
            echo "### $module" >> "$OUTPUT"
            echo "" >> "$OUTPUT"
            if [ -n "$events" ]; then
                echo "**Définis :**" >> "$OUTPUT"
                echo "$events" | while read -r e; do echo "- \`$e\`" >> "$OUTPUT"; done
                echo "" >> "$OUTPUT"
            fi
            if [ -n "$dispatched" ]; then
                echo "**Dispatchés depuis ce module :**" >> "$OUTPUT"
                echo "$dispatched" | while read -r e; do echo "- \`$e\`" >> "$OUTPUT"; done
                echo "" >> "$OUTPUT"
            fi
        fi
    done
fi

cat >> "$OUTPUT" <<EOF

## Listeners (par module)

EOF

if [ -d "Modules" ]; then
    for dir in Modules/*/; do
        module=$(basename "$dir")
        listeners=$(find "$dir/Listeners" -name "*.php" -type f 2>/dev/null | sed "s|.*/||;s|\.php$||" | sort -u || true)
        if [ -n "$listeners" ]; then
            echo "### $module" >> "$OUTPUT"
            echo "" >> "$OUTPUT"
            echo "$listeners" | while read -r l; do echo "- \`$l\`" >> "$OUTPUT"; done
            echo "" >> "$OUTPUT"
        fi
    done
fi

cat >> "$OUTPUT" <<EOF

---

## Convention

Un événement de domaine doit :

- vivre dans \`Modules/<X>/Domain/Events/\` ou \`Modules/<X>/Events/\`
- être nommé au passé (\`OrderConfirmed\`, \`StockDecremented\`, \`InvoiceIssued\`)
- être immutable (constructeur readonly + propriétés publiques readonly)
- contenir \`instance_id\` pour permettre le scoping côté listener
- être documenté ici dès sa création

Un listener doit :

- être idempotent
- traiter les exceptions sans propager si non récupérable
- être testé avec un événement faux
EOF

echo -e "${GREEN}[OK]${NC} $OUTPUT régénéré."
