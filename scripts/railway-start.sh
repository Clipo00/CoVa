#!/bin/bash
set -e

echo "=== CoVa Railway Start ==="

echo "[1/3] Running migrations..."
php artisan migrate --force

echo "[2/3] Building CLI PHAR..."
cd /app/cli
composer install --no-dev --optimize-autoloader --no-interaction
php -d phar.readonly=0 build-phar.php
cd /app
mkdir -p public/downloads
cp cli/builds/covar public/downloads/covar.phar

echo "[3/3] Starting PHP-FPM + Nginx..."
exec /start-container.sh
