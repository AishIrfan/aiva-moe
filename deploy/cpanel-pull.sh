#!/usr/bin/env bash
# AIVA MOE · cPanel server-side deploy
# Run on the server (cPanel Terminal / SSH) inside the repo directory.
# Pulls latest main, installs deps, migrates, and refreshes caches.
# Usage: bash deploy/cpanel-pull.sh
#
# If you see errors like:
#     deploy/cpanel-pull.sh: line 6: $'\r': command not found
#     : invalid option name: line 7: set: pipefail
# the file has CRLF line endings (typically introduced by editing via
# cPanel File Manager or uploading via FTP in text mode). One-time fix:
#     sed -i 's/\r$//' deploy/cpanel-pull.sh && bash deploy/cpanel-pull.sh
# After that, the script's `git reset --hard origin/main` below keeps
# the file synced to git's canonical LF version on every successful run.

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

echo "▶ Seeding (idempotent — firstOrCreate / updateOrCreate throughout)…"
php artisan db:seed --force

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
