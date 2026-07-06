#!/bin/bash
set -e

echo "=== CoVa Railway Build ==="

# --- Standard Laravel build (mirrors Railpack auto-detection) ---
echo "[1/4] Installing PHP dependencies..."
composer install --optimize-autoloader --no-dev --no-interaction

echo "[2/4] Installing & building frontend..."
npm ci
npm run build

echo "[3/4] Laravel optimization..."
mkdir -p storage/framework/{sessions,views,cache,testing} storage/logs bootstrap/cache
chmod -R a+rw storage bootstrap/cache
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# --- CLI PHAR build ---
echo "[4/4] Building CLI PHAR..."
cd cli
composer install --no-dev --optimize-autoloader --no-interaction
php -d phar.readonly=0 build-phar.php
cd ..

mkdir -p public/downloads
cp cli/builds/covar public/downloads/covar.phar

echo "=== Build complete ==="
