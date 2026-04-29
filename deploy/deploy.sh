#!/usr/bin/env bash
# AIVA MOE · LFTP deploy script
# Pushes the built Laravel app to the remote host configured in .env (LFTP_*).
# Run: bash deploy/deploy.sh

set -euo pipefail

HERE="$(cd "$(dirname "$0")/.." && pwd)"
cd "$HERE"

# Load .env
set -a; . ./.env; set +a

: "${LFTP_HOST:?LFTP_HOST missing}"
: "${LFTP_USERNAME:?LFTP_USERNAME missing}"
: "${LFTP_PASSWORD:?LFTP_PASSWORD missing}"
: "${LFTP_PATH:?LFTP_PATH missing}"

echo "▶ Installing production dependencies…"
composer install --no-dev --optimize-autoloader --no-interaction

echo "▶ Building frontend assets…"
npm ci --no-audit --no-fund
npm run build

echo "▶ Caching framework…"
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache || true

echo "▶ Running migrations against remote (must be reachable from build host)…"
# Comment out if your deploy host can't reach the DB — run migrations on the server instead.
# php artisan migrate --force

echo "▶ Uploading via LFTP → ${LFTP_HOST}:${LFTP_PATH}"
lftp -u "${LFTP_USERNAME},${LFTP_PASSWORD}" "${LFTP_HOST}" <<EOF
set ftp:ssl-allow no
set ssl:verify-certificate no
mirror -R --delete --verbose \
    --exclude-glob .git/ \
    --exclude-glob .github/ \
    --exclude-glob node_modules/ \
    --exclude-glob tests/ \
    --exclude-glob deploy/ \
    --exclude-glob storage/framework/cache/ \
    --exclude-glob storage/framework/sessions/ \
    --exclude-glob storage/framework/views/ \
    --exclude-glob storage/logs/ \
    --exclude-glob database/database.sqlite \
    --exclude-glob .env \
    ./ ${LFTP_PATH}
bye
EOF

echo "✔ Deploy uploaded. On the server, run: php artisan migrate --force && php artisan storage:link"
