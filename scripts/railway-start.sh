#!/bin/bash
set -e

echo "=== CoVa Railway Start ==="

echo "[1/4] Installing system tools..."
apt-get update -qq && apt-get install -y -qq mysql-client gzip 2>/dev/null || true

echo "[2/4] Running migrations..."
php artisan migrate --force

echo "[3/4] Building CLI PHAR..."
cd /app/cli
composer install --no-dev --optimize-autoloader --no-interaction
php -d phar.readonly=0 build-phar.php
cd /app
mkdir -p public/downloads
cp cli/builds/covar public/downloads/covar.phar

echo "[4/4] Starting scheduler + server..."
# Run Laravel scheduler every minute in background
while true; do php artisan schedule:run --verbose --no-interaction >> /dev/null 2>&1; sleep 60; done &

exec /start-container.sh
