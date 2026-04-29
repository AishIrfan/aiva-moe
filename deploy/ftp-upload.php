<?php
// FTPS uploader for AIVA MOE Laravel app → shared host.
// Uses explicit FTPS on port 21 (confirmed working against 103.191.76.66).
// Usage: php deploy/ftp-upload.php [--dry-run] [--only=vendor]

declare(strict_types=1);
ini_set('display_errors', '1');
error_reporting(E_ALL);

$root = realpath(__DIR__.'/..');
chdir($root);

// --- Load .env + .env.production; later values override.
$env = [];
foreach (['.env', '.env.production'] as $f) {
    if (! file_exists($root.'/'.$f)) continue;
    foreach (file($root.'/'.$f, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (preg_match('/^\s*([A-Z0-9_]+)\s*=\s*(.*)$/', $line, $m)) {
            $env[$m[1]] = trim($m[2], " \t\"'");
        }
    }
}

$host = $env['LFTP_HOST'] ?? die("LFTP_HOST missing\n");
$user = $env['LFTP_USERNAME'] ?? die("LFTP_USERNAME missing\n");
$pass = $env['LFTP_PASSWORD'] ?? die("LFTP_PASSWORD missing\n");
$remoteBase = rtrim($env['LFTP_PATH'] ?? die("LFTP_PATH missing\n"), '/');

$dryRun = in_array('--dry-run', $argv, true);
$only = null;
foreach ($argv as $a) if (str_starts_with($a, '--only=')) $only = substr($a, 7);

// --- Exclusion rules (paths relative to app root, forward slashes)
$excludeExact = [
    '.env',          // we'll upload .env.production → remote .env separately
    '.env.example',
    '.env.production',
    '.gitattributes',
    '.gitignore',
    'DEVELOPMENT_CHECKLIST.md',
    'README.md',
    'phpunit.xml',
    'database/database.sqlite',
    'package-lock.json',
];

$excludeDirPrefixes = [
    '.git/',
    '.github/',
    '.idea/',
    '.vscode/',
    'node_modules/',
    'tests/',
    'deploy/',
    'storage/framework/cache/',
    'storage/framework/sessions/',
    'storage/framework/views/',
    'storage/logs/',
    'storage/app/private/',
];

// Files matching these are skipped
$excludeBasename = ['.DS_Store', 'Thumbs.db', 'error_log'];

// --- Gather file list
echo "scanning {$root}…\n";
$files = [];
$it = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root, RecursiveDirectoryIterator::SKIP_DOTS | FilesystemIterator::UNIX_PATHS),
    RecursiveIteratorIterator::SELF_FIRST
);
foreach ($it as $info) {
    if (! $info->isFile()) continue;
    $rel = str_replace('\\', '/', substr($info->getPathname(), strlen($root) + 1));
    if (in_array($rel, $excludeExact, true)) continue;
    if (in_array(basename($rel), $excludeBasename, true)) continue;
    // Keep .gitignore files everywhere (they preserve directory structure on remote).
    if (basename($rel) !== '.gitignore') {
        foreach ($excludeDirPrefixes as $p) if (str_starts_with($rel, $p)) continue 2;
    }
    if ($only !== null && ! str_starts_with($rel, $only)) continue;
    $files[$rel] = $info->getSize();
}

// Always include .env (upload .env.production as .env remotely)
$files['__ENV_RENAME__'] = filesize($root.'/.env.production');
// Preserve storage structure (empty .gitkeep files are already included if present)

ksort($files);
$totalBytes = array_sum($files);
$nFiles = count($files);
echo "→ {$nFiles} files, ".number_format($totalBytes / 1048576, 2)." MiB total\n";
if ($dryRun) {
    foreach ($files as $f => $sz) echo "  $f ($sz B)\n";
    exit(0);
}

// --- Connect
function connect(string $host, string $user, string $pass) {
    for ($i = 0; $i < 3; $i++) {
        $c = @ftp_ssl_connect($host, 21, 30);
        if (! $c) { echo "  connect retry ".($i+1)."…\n"; sleep(2); continue; }
        if (! @ftp_login($c, $user, $pass)) { echo "  login retry ".($i+1)."…\n"; @ftp_close($c); sleep(2); continue; }
        ftp_pasv($c, true);
        ftp_set_option($c, FTP_USECLIENTCERT, false);
        return $c;
    }
    die("FTP connect/login failed after 3 attempts.\n");
}

echo "connecting FTPS to {$host}…\n";
$ftp = connect($host, $user, $pass);
echo "connected. remote base = {$remoteBase}\n";

// Ensure remote base exists
@ftp_mkdir($ftp, $remoteBase);

// Cache directories we've already created
$madeDirs = [$remoteBase => true];
function ensureDir($ftp, string $remote, array &$madeDirs): void {
    if (isset($madeDirs[$remote])) return;
    $parent = substr($remote, 0, strrpos($remote, '/'));
    if ($parent && $parent !== '/' && $parent !== '') ensureDir($ftp, $parent, $madeDirs);
    @ftp_mkdir($ftp, $remote); // silent — may already exist
    $madeDirs[$remote] = true;
}

// --- Upload loop
$uploaded = 0;
$bytesDone = 0;
$failed = [];
$started = microtime(true);

foreach ($files as $rel => $size) {
    if ($rel === '__ENV_RENAME__') {
        $localFile = $root.'/.env.production';
        $remoteFile = $remoteBase.'/.env';
    } else {
        $localFile = $root.'/'.$rel;
        $remoteFile = $remoteBase.'/'.$rel;
    }

    $remoteDir = substr($remoteFile, 0, strrpos($remoteFile, '/'));
    ensureDir($ftp, $remoteDir, $madeDirs);

    $ok = false;
    for ($attempt = 1; $attempt <= 3; $attempt++) {
        $ok = @ftp_put($ftp, $remoteFile, $localFile, FTP_BINARY);
        if ($ok) break;
        // reconnect on failure
        @ftp_close($ftp);
        $ftp = connect($env['LFTP_HOST'], $env['LFTP_USERNAME'], $env['LFTP_PASSWORD']);
        $madeDirs = [$remoteBase => true];
        ensureDir($ftp, $remoteDir, $madeDirs);
    }

    if ($ok) {
        $uploaded++;
        $bytesDone += $size;
        if ($uploaded % 40 === 0 || $uploaded === $nFiles) {
            $pct = $nFiles ? round($uploaded / $nFiles * 100) : 100;
            $mb = number_format($bytesDone / 1048576, 1);
            $elapsed = microtime(true) - $started;
            $rate = $elapsed ? number_format($bytesDone / 1048576 / $elapsed, 2) : '—';
            echo sprintf("  [%3d%%] %5d/%d files · %s MiB · %s MiB/s · last: %s\n",
                $pct, $uploaded, $nFiles, $mb, $rate, $rel);
        }
    } else {
        $failed[] = $rel;
        echo "  ✘ FAILED: {$rel}\n";
    }
}

@ftp_close($ftp);
echo "\n";
echo "uploaded: {$uploaded}/{$nFiles}\n";
if ($failed) {
    echo "FAILED (".count($failed)."):\n";
    foreach ($failed as $f) echo "  $f\n";
    exit(1);
}
echo "✅ Upload complete. Now hit:\n";
echo "   http://moe-laravel.weststar-dev.com/install.php?token={$env['INSTALL_TOKEN']}&seed=1\n";
