#!/bin/bash
set -e

echo "=== CoVa Railway Start ==="

echo "[1/6] Installing system tools..."
apt-get update -qq && apt-get install -y -qq mysql-client gzip 2>/dev/null || true

echo "[2/6] Clearing stale caches (fixes Livewire 405)..."
php artisan optimize:clear

echo "[3/7] Running migrations..."
php artisan migrate --force

echo "[4/7] Seeding data..."
php artisan db:seed --force

echo "[5/7] Building CLI PHAR..."
cd /app/cli
composer install --no-dev --optimize-autoloader --no-interaction
php -d phar.readonly=0 build-phar.php
cd /app
mkdir -p public/downloads
cp cli/builds/covar public/downloads/covar.phar

echo "[6/7] Starting scheduler in background..."
# Run Laravel scheduler every minute
while true; do php artisan schedule:run --verbose --no-interaction >> /dev/null 2>&1; sleep 60; done &

echo "[7/7] Starting PHP-FPM + Nginx..."
exec /start-container.sh
