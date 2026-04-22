#!/usr/bin/env bash
# scripts/quality/audit-dependencies.sh
# Audit des dépendances entre modules et entre couches.

set -euo pipefail

REPO_ROOT="$(git rev-parse --show-toplevel)"
cd "$REPO_ROOT"

GREEN='\033[0;32m'
NC='\033[0m'

echo "════════════════════════════════════════════════════════════════"
echo " Audit des dépendances inter-modules"
echo "════════════════════════════════════════════════════════════════"
echo ""

if [ -x "vendor/bin/deptrac" ]; then
    vendor/bin/deptrac analyse --report-uncovered --no-progress
else
    echo "Deptrac non installé. Installe : composer require --dev qossmic/deptrac-shim"
    exit 1
fi

echo ""
echo "────────────────────────────────────────────────────────────────"
echo " Imports cross-module détectés (use Modules\\X\\... depuis Modules\\Y)"
echo "────────────────────────────────────────────────────────────────"
echo ""

if [ -d "Modules" ]; then
    for source_dir in Modules/*/; do
        source_module=$(basename "$source_dir")
        for target_dir in Modules/*/; do
            target_module=$(basename "$target_dir")
            [ "$source_module" = "$target_module" ] && continue

            count=$(grep -rh "use Modules\\\\$target_module\\\\" "$source_dir" 2>/dev/null | wc -l | tr -d ' ')
            if [ "$count" -gt 0 ]; then
                echo "  $source_module → $target_module : $count imports"
            fi
        done
    done
fi

echo ""
echo -e "${GREEN}>>>${NC} Pour le graphe visuel : make deptrac-graph"
