<?php
// Incremental deploy: upload a list of changed files and clear Laravel's bootstrap caches
// so the new code takes effect immediately.

declare(strict_types=1);
ini_set('display_errors', '1');
error_reporting(E_ALL);

$root = realpath(__DIR__.'/..');
chdir($root);

$env = [];
foreach (['.env', '.env.production'] as $f) {
    if (! file_exists($root.'/'.$f)) continue;
    foreach (file($root.'/'.$f, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (preg_match('/^\s*([A-Z0-9_]+)\s*=\s*(.*)$/', $line, $m)) {
            $env[$m[1]] = trim($m[2], " \t\"'");
        }
    }
}

$host = $env['LFTP_HOST'];
$user = $env['LFTP_USERNAME'];
$pass = $env['LFTP_PASSWORD'];
$remoteBase = rtrim($env['LFTP_PATH'], '/');

// Files to push (relative to app root)
$changed = [
    'app/Http/Controllers/School/SchoolContextController.php',
    'app/Http/Controllers/School/AlertsController.php',
    'app/Http/Controllers/School/CameraController.php',
    'app/Http/Controllers/School/DocumentsController.php',
    'app/Http/Controllers/School/StudentsController.php',
    'app/Http/Controllers/School/EnrollmentController.php',
    'app/Http/Controllers/School/GradesClassesController.php',
    'app/Http/Controllers/School/AssistanceController.php',
    'app/Http/Controllers/School/EventsManagementController.php',
    'app/Http/Controllers/School/LeaveManagementController.php',
    'app/Http/Controllers/School/DisciplineController.php',
    'app/Http/Controllers/School/ScheduleController.php',
    'app/Http/Controllers/School/ChatController.php',
    'app/Http/Controllers/School/LeavesController.php',
    'app/Http/Controllers/School/SettingsController.php',
    'app/Http/Middleware/SetMode.php',
    'app/Providers/AppServiceProvider.php',
    'resources/views/partials/topbar.blade.php',
];

// Server cache files to clear so the new code takes effect.
// Laravel regenerates these lazily — deleting is safe.
$cacheFilesToRemove = [
    'bootstrap/cache/config.php',
    'bootstrap/cache/routes-v7.php',
    'bootstrap/cache/services.php',
    'bootstrap/cache/packages.php',
];

// Compiled views (Blade). Laravel regenerates based on mtime, but we force by globbing.
$remoteViewCacheDir = $remoteBase.'/storage/framework/views';

echo "connecting FTPS to {$host}…\n";
$ftp = ftp_ssl_connect($host, 21, 30);
if (! $ftp || ! ftp_login($ftp, $user, $pass)) die("ftp login failed\n");
ftp_pasv($ftp, true);

$upOk = 0; $upFail = 0;
foreach ($changed as $rel) {
    $local = $root.'/'.$rel;
    if (! is_file($local)) { echo "  MISSING: $rel\n"; continue; }
    $remote = $remoteBase.'/'.$rel;
    // ensure remote dir exists (best-effort)
    $dirs = explode('/', dirname($rel));
    $p = $remoteBase;
    foreach ($dirs as $d) { $p .= '/'.$d; @ftp_mkdir($ftp, $p); }

    if (@ftp_put($ftp, $remote, $local, FTP_BINARY)) {
        $upOk++;
        echo "  ✔ $rel\n";
    } else {
        $upFail++;
        echo "  ✘ $rel\n";
    }
}

echo "\nclearing server caches…\n";
foreach ($cacheFilesToRemove as $rel) {
    $ok = @ftp_delete($ftp, $remoteBase.'/'.$rel);
    echo ($ok ? "  ✔ removed" : "  · absent")." $rel\n";
}

// Sweep compiled views
$views = @ftp_nlist($ftp, $remoteViewCacheDir);
$vCount = 0;
if (is_array($views)) {
    foreach ($views as $v) {
        if (str_ends_with($v, '.php')) {
            if (@ftp_delete($ftp, $v)) $vCount++;
        }
    }
}
echo "  ✔ cleared {$vCount} compiled views\n";

@ftp_close($ftp);
echo "\nuploaded: {$upOk} · failed: {$upFail}\n";
echo "✅ Patch deployed. Laravel will regenerate caches on next request.\n";
