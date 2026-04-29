#!/usr/bin/env bash
# AIVA MOE · cPanel server-side deploy
# Run on the server (cPanel Terminal / SSH) inside the repo directory.
# Pulls latest main, installs deps, migrates, and refreshes caches.
# Usage: bash deploy/cpanel-pull.sh

set -euo pipefail

HERE="$(cd "$(dirname "$0")/.." && pwd)"
cd "$HERE"

echo "▶ Pulling latest from origin/main…"
git fetch origin
git reset --hard origin/main

echo "▶ Installing PHP dependencies (production)…"
composer install --no-dev --optimize-autoloader --no-interaction

echo "▶ Clearing stale caches…"
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

echo "▶ Running migrations…"
php artisan migrate --force

echo "▶ Re-caching for production…"
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache || true

echo "▶ Storage symlink…"
php artisan storage:link || true

echo "▶ Permissions…"
chmod -R 775 storage bootstrap/cache

if command -v npm >/dev/null 2>&1; then
    echo "▶ Building frontend assets…"
    npm ci --no-audit --no-fund
    npm run build
else
    echo "⚠ npm not available on this host — upload public/build/ manually."
fi

echo "✔ Deploy complete."
