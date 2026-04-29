# Deployment

## One-time server setup (after first upload)

The LFTP target `/public_html/moe-laravel.weststar-dev.com` is a web document root. Laravel's front controller lives in `public/`. On shared hosting you typically need ONE of:

1. **Document root = Laravel `public/`** — point the vhost at `/public_html/moe-laravel.weststar-dev.com/public`, then `LFTP_PATH` should upload to the directory *above* it (already does).
2. **If vhost cannot be changed** — add a tiny `index.php` + `.htaccess` in the LFTP target that rewrites to `public/`. Example `.htaccess` at the target root:

```apache
RewriteEngine On
RewriteRule ^(.*)$ public/$1 [L]
```

## Deploy steps (locally, then via LFTP)

```bash
# Unix / WSL
bash deploy/deploy.sh

# Windows
powershell -File deploy\deploy.ps1
```

The script runs: `composer install --no-dev`, `npm ci && npm run build`, `artisan config/route/view:cache`, and uploads via `lftp` using the `LFTP_*` vars in `.env`.

## First-time server tasks

```bash
# SSH into the server, then:
cd /public_html/moe-laravel.weststar-dev.com
php artisan migrate --force
php artisan storage:link
chmod -R 775 storage bootstrap/cache
```

## Switching to MySQL

In `.env` on the server (not the local one):

```
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=weststar_moe-laravel
DB_USERNAME=weststar_admin
DB_PASSWORD=...
```

Then `php artisan migrate --force` again.

## Smoke test after deploy

- `GET /` — redirects `/login` when not authed
- `GET /login` — 200
- Log in as `admin@aiva.test` / `password` (seeded)
- `POST /api/fr/trigger` with a JSON payload should return `{ "ok": true }`
