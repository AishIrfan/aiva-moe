# AIVA MOE · Windows deploy script (PowerShell)
# Builds + uploads via WinSCP (or falls back to lftp via WSL).
# Run: powershell -File deploy\deploy.ps1

$ErrorActionPreference = 'Stop'
Set-Location (Join-Path $PSScriptRoot '..')

# Load .env
Get-Content .env | Where-Object { $_ -match '^\s*[^#].*=' } | ForEach-Object {
    $k,$v = $_ -split '=',2
    Set-Item -Path "Env:$($k.Trim())" -Value $v.Trim('"').Trim()
}

Write-Host '▶ composer install --no-dev --optimize-autoloader'
composer install --no-dev --optimize-autoloader --no-interaction

Write-Host '▶ npm ci && npm run build'
npm ci --no-audit --no-fund
npm run build

Write-Host '▶ artisan caches'
php artisan config:cache
php artisan route:cache
php artisan view:cache

Write-Host "▶ Upload to $env:LFTP_HOST$env:LFTP_PATH via LFTP (requires WSL or lftp in PATH)"
$lftpScript = @"
open -u $env:LFTP_USERNAME,$env:LFTP_PASSWORD $env:LFTP_HOST
set ftp:ssl-allow no
mirror -R --delete \
    --exclude-glob .git/ \
    --exclude-glob node_modules/ \
    --exclude-glob tests/ \
    --exclude-glob deploy/ \
    --exclude-glob storage/framework/cache/ \
    --exclude-glob storage/framework/sessions/ \
    --exclude-glob storage/framework/views/ \
    --exclude-glob storage/logs/ \
    --exclude-glob database/database.sqlite \
    --exclude-glob .env \
    ./ $env:LFTP_PATH
bye
"@
$lftpScript | lftp
Write-Host '✔ Upload complete.'
