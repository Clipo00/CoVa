#!/bin/bash
set -e

echo "=== CoVa Railway Build ==="

# Note: Railpack already ran composer install, npm ci, and npm prune
# in the install:composer, install:node, and prune:node steps.
# We only do additional steps here.

echo "[1/4] Removing dev dependencies..."
composer install --optimize-autoloader --no-dev --no-interaction

echo "[2/4] Building frontend..."
# Railpack's prune step leaves stale .cache — nuke it
rm -rf /app/node_modules/.cache
npm run build

echo "[3/4] Laravel optimization..."
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

echo "[4/4] Building CLI PHAR..."
cd cli
composer install --no-dev --optimize-autoloader --no-interaction
php -d phar.readonly=0 build-phar.php
cd ..
mkdir -p public/downloads
cp cli/builds/covar public/downloads/covar.phar

echo "=== Build complete ==="
