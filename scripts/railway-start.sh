#!/bin/bash
set -e

echo "=== CoVa Railway Start ==="

echo "[1/2] Running migrations..."
php artisan migrate --force

echo "[2/2] Starting PHP-FPM + Nginx..."
exec /start-container.sh
