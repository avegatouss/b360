#!/usr/bin/env bash
# scripts/quality/scaffold-new-module.sh
# Complète le squelette d'un module nwidart avec la structure B360 (DDD light).

set -euo pipefail

MODULE="${1:-}"
if [ -z "$MODULE" ]; then
    echo "Usage : bash scripts/quality/scaffold-new-module.sh <NomModule>"
    exit 1
fi

REPO_ROOT="$(git rev-parse --show-toplevel)"
cd "$REPO_ROOT"

MODULE_DIR="Modules/$MODULE"
[ -d "$MODULE_DIR" ] || { echo "Module $MODULE introuvable. Lance d'abord : php artisan module:make $MODULE"; exit 1; }

GREEN='\033[0;32m'
NC='\033[0m'

mkdir -p "$MODULE_DIR"/{Application/{Commands,Queries,Services,DTO,Policies},Domain/{Models,ValueObjects,Events,Exceptions,Contracts},Infrastructure/{Persistence,Repositories,Mappers,Jobs}}

# README minimal
[ -f "$MODULE_DIR/README.md" ] || cat > "$MODULE_DIR/README.md" <<EOF
# $MODULE

> Module B360. À compléter.

## Responsabilités

À définir.

## Dépendances

Voir \`module.json\` et \`docs/architecture/MODULE_DEPENDENCY_MAP.md\`.

## Capacités exposées

- (à compléter)

## Permissions

Déclarées via HookRegistry dans le ServiceProvider.

## Événements publiés / consommés

Documentés dans \`docs/index/EVENT_INDEX.md\`.

## Tests

\`\`\`
php artisan test Modules/$MODULE/Tests --parallel
\`\`\`
EOF

# SPEC initial
[ -f "$MODULE_DIR/SPEC.md" ] || cat > "$MODULE_DIR/SPEC.md" <<EOF
# $MODULE — Spécification

## Objectif

À définir.

## Périmètre fonctionnel

À définir.

## Règles métier

- (à compléter)

## États / transitions

- (à compléter)

## Invariants

- (à compléter)

## Contrats publiés

- (à compléter)
EOF

# CHANGELOG
[ -f "$MODULE_DIR/CHANGELOG.md" ] || cat > "$MODULE_DIR/CHANGELOG.md" <<EOF
# $MODULE — Changelog

## [non publié]

- Création du squelette
EOF

echo -e "${GREEN}[scaffold]${NC} Squelette $MODULE complété (Application/Domain/Infrastructure + README/SPEC/CHANGELOG)."
echo ""
echo "Pense à :"
echo "  1. Adapter tools/deptrac/deptrac.yaml pour ajouter la couche $MODULE"
echo "  2. Ajouter le module dans docs/index/MODULE_INDEX.md"
echo "  3. Créer un ADR si nouvelle couche"
