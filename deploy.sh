#!/bin/bash
set -e

#
# B360 Deployment Script
# Usage: ./deploy.sh [--skip-npm] [--no-migrate]
#

SKIP_NPM=false
NO_MIGRATE=false

for arg in "$@"; do
    case $arg in
        --skip-npm)    SKIP_NPM=true ;;
        --no-migrate)  NO_MIGRATE=true ;;
        --help)
            echo "Usage: ./deploy.sh [OPTIONS]"
            echo ""
            echo "Options:"
            echo "  --skip-npm     Skip NPM install and asset build"
            echo "  --no-migrate   Skip database migrations"
            echo "  --help         Show this help message"
            exit 0
            ;;
    esac
done

echo "=========================================="
echo " B360 Deployment"
echo "=========================================="
echo ""

# Ensure we are in the project directory
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

echo "[1/8] Entering maintenance mode..."
php artisan down --retry=60

echo "[2/8] Pulling latest code..."
git pull origin main

echo "[3/8] Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

if [ "$SKIP_NPM" = false ]; then
    echo "[4/8] Installing NPM dependencies..."
    npm ci

    echo "[5/8] Building frontend assets..."
    npm run build
else
    echo "[4/8] Skipping NPM install (--skip-npm)"
    echo "[5/8] Skipping asset build (--skip-npm)"
fi

if [ "$NO_MIGRATE" = false ]; then
    echo "[6/8] Running database migrations..."
    php artisan migrate --force
else
    echo "[6/8] Skipping migrations (--no-migrate)"
fi

echo "[7/8] Rebuilding caches..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

echo "[8/8] Restarting queue workers..."
php artisan queue:restart

# Exit maintenance mode
php artisan up

echo ""
echo "=========================================="
echo " Deployment complete!"
echo "=========================================="
