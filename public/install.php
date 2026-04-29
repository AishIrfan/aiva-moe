<?php
// One-shot post-deploy installer. Hit once via HTTP, then DELETE this file.
// URL: http(s)://moe-laravel.weststar-dev.com/install.php?token=<INSTALL_TOKEN from .env>

declare(strict_types=1);
ini_set('display_errors', '1');
error_reporting(E_ALL);
set_time_limit(300);
header('Content-Type: text/plain; charset=utf-8');

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$expected = env('INSTALL_TOKEN');
$given = $_GET['token'] ?? '';
if (! $expected || ! hash_equals((string) $expected, (string) $given)) {
    http_response_code(403);
    exit("Forbidden. Provide ?token=... matching INSTALL_TOKEN in .env\n");
}

function step(string $label, callable $fn): void {
    echo "▶ {$label}\n";
    try {
        $out = $fn();
        if ($out) echo trim((string) $out)."\n";
        echo "  ✔ done\n\n";
    } catch (\Throwable $e) {
        echo "  ✘ FAILED: ".$e->getMessage()."\n";
        echo "  ".get_class($e).' @ '.$e->getFile().':'.$e->getLine()."\n\n";
    }
}

echo "AIVA MOE installer\n";
echo "==================\n";
echo "PHP ".PHP_VERSION."\n";
echo "Laravel ".app()->version()."\n";
echo "ENV=".app()->environment()."\n";
echo "DB=".config('database.default')."\n\n";

step('Clearing stale caches', function () {
    Illuminate\Support\Facades\Artisan::call('config:clear');
    Illuminate\Support\Facades\Artisan::call('route:clear');
    Illuminate\Support\Facades\Artisan::call('view:clear');
    return Illuminate\Support\Facades\Artisan::output();
});

step('Checking database connection', function () {
    $pdo = DB::connection()->getPdo();
    return 'Connected to '.DB::connection()->getDatabaseName().' as '.($pdo::ATTR_DRIVER_NAME);
});

step('Running migrations (--force)', function () {
    Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
    return Illuminate\Support\Facades\Artisan::output();
});

if (isset($_GET['seed'])) {
    step('Seeding database', function () {
        Illuminate\Support\Facades\Artisan::call('db:seed', ['--force' => true]);
        return Illuminate\Support\Facades\Artisan::output();
    });
}

step('Linking storage (public/storage → storage/app/public)', function () {
    $target = realpath(__DIR__.'/../storage/app/public') ?: __DIR__.'/../storage/app/public';
    $link = __DIR__.'/storage';
    if (! is_dir($target)) @mkdir($target, 0775, true);
    if (file_exists($link) || is_link($link)) return 'link already exists';
    // Use relative symlink first (works on most shared hosts), fall back to PHP copy
    if (@symlink('../storage/app/public', $link)) return 'symlink created';
    // Fallback: create a folder and tell the user
    @mkdir($link, 0775, true);
    return 'symlink not permitted — created plain folder; uploads may need post-copy';
});

step('Writing ownership-safe permissions', function () {
    foreach ([__DIR__.'/../storage', __DIR__.'/../bootstrap/cache'] as $dir) {
        if (is_dir($dir)) @chmod($dir, 0775);
    }
    return 'storage + bootstrap/cache → 0775';
});

step('Caching config / routes / views', function () {
    Illuminate\Support\Facades\Artisan::call('config:cache');
    Illuminate\Support\Facades\Artisan::call('route:cache');
    Illuminate\Support\Facades\Artisan::call('view:cache');
    return Illuminate\Support\Facades\Artisan::output();
});

echo "\n✅ Install complete.\n";
echo "DELETE this file now: public/install.php (or hit ?token=...&delete=1)\n";

if (($_GET['delete'] ?? '') === '1') {
    @unlink(__FILE__);
    echo "install.php self-deleted.\n";
}
