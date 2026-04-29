<?php
// One-shot cache rebuild. Upload, hit once, delete.
// URL: /_reload.php?token=<INSTALL_TOKEN>
declare(strict_types=1);
ini_set('display_errors', '1');
error_reporting(E_ALL);
set_time_limit(120);
header('Content-Type: text/plain; charset=utf-8');

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Read INSTALL_TOKEN from .env directly so this still works even if config is cached.
$expected = null;
$envPath = __DIR__.'/../.env';
if (is_file($envPath)) {
    foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (preg_match('/^\s*INSTALL_TOKEN\s*=\s*(.*)$/', $line, $m)) {
            $expected = trim($m[1], " \t\"'");
            break;
        }
    }
}
$given = $_GET['token'] ?? '';
if (! $expected || ! hash_equals((string) $expected, (string) $given)) {
    http_response_code(403);
    exit("Forbidden.\n");
}

function step(string $label, callable $fn): void {
    echo "▶ {$label}\n";
    try {
        $out = $fn();
        if ($out) echo trim((string) $out)."\n";
        echo "  ✔ done\n\n";
    } catch (\Throwable $e) {
        echo "  ✘ FAILED: ".$e->getMessage()."\n\n";
    }
}

echo "Cache rebuild\n============\n";
echo "PHP ".PHP_VERSION."\n";
echo "Laravel ".app()->version()."\n";
echo "ENV=".app()->environment()."\n\n";

step('optimize:clear', function () {
    Illuminate\Support\Facades\Artisan::call('optimize:clear');
    return Illuminate\Support\Facades\Artisan::output();
});

step('config:cache', function () {
    Illuminate\Support\Facades\Artisan::call('config:cache');
    return Illuminate\Support\Facades\Artisan::output();
});

step('route:cache', function () {
    Illuminate\Support\Facades\Artisan::call('route:cache');
    return Illuminate\Support\Facades\Artisan::output();
});

step('view:cache', function () {
    Illuminate\Support\Facades\Artisan::call('view:cache');
    return Illuminate\Support\Facades\Artisan::output();
});

step('Checking app key + DB + users', function () {
    $row = DB::table('users')->select(['id', 'email', 'role'])->limit(20)->get();
    return 'DB OK. Users: '.$row->count().' — '.$row->pluck('email')->join(', ');
});

if (isset($_GET['seed'])) {
    step('Running DatabaseSeeder', function () {
        Illuminate\Support\Facades\Artisan::call('db:seed', ['--force' => true]);
        return Illuminate\Support\Facades\Artisan::output();
    });

    step('Re-listing users after seed', function () {
        $row = DB::table('users')->select(['id', 'email', 'role'])->get();
        $lines = $row->map(fn ($r) => "  #{$r->id} {$r->email} · {$r->role}")->join("\n");
        return $row->count()." users:\n".$lines;
    });
}

echo "\n✅ Rebuild complete. DELETE _reload.php now.\n";

if (($_GET['delete'] ?? '') === '1') {
    @unlink(__FILE__);
    echo "Self-deleted.\n";
}
