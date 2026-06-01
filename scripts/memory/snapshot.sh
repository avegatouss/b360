#!/usr/bin/env bash
# scripts/memory/snapshot.sh
# Snapshot horodaté de la mémoire projet (utile avant un gros lot pour rollback).

set -euo pipefail

REPO_ROOT="$(git rev-parse --show-toplevel)"
cd "$REPO_ROOT"

GREEN='\033[0;32m'
NC='\033[0m'

TS=$(date +%Y%m%d-%H%M%S)
SNAP_DIR="docs/memory/.snapshots/$TS"

mkdir -p "$SNAP_DIR"
cp -r docs/memory/*.md docs/context docs/index "$SNAP_DIR/" 2>/dev/null || true

echo -e "${GREEN}[snapshot]${NC} Snapshot créé : $SNAP_DIR"
echo "Pour restaurer : cp -r $SNAP_DIR/*.md docs/memory/"

# Limiter à 20 derniers snapshots
ls -dt docs/memory/.snapshots/* 2>/dev/null | tail -n +21 | xargs -r rm -rf
