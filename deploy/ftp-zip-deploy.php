<?php
// Fast deployer: build one ZIP of the app, upload it, then trigger extract+install via HTTP.
// Replaces per-file FTP (slow on small-file-heavy Laravel projects).

declare(strict_types=1);
ini_set('display_errors', '1');
error_reporting(E_ALL);

$root = realpath(__DIR__.'/..');
chdir($root);

// Load env
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
$token = $env['INSTALL_TOKEN'] ?? bin2hex(random_bytes(8));

// --- Exclusion
$excludeExact = [
    '.env', '.env.example', '.env.production', '.gitattributes', '.gitignore',
    'DEVELOPMENT_CHECKLIST.md', 'README.md', 'phpunit.xml',
    'database/database.sqlite', 'package-lock.json',
];
$excludeDirPrefixes = [
    '.git/', '.github/', '.idea/', '.vscode/', 'node_modules/',
    'tests/', 'deploy/',
    'storage/framework/cache/data/',
    'storage/framework/sessions/',
    'storage/framework/views/',
    'storage/logs/',
    'storage/app/private/',
    'storage/app/public/',
];
$excludeBasename = ['.DS_Store', 'Thumbs.db', 'error_log', 'laravel.log'];

// --- Build ZIP
$zipPath = sys_get_temp_dir().'/moe-deploy-'.date('YmdHis').'.zip';
echo "building {$zipPath}…\n";
$z = new ZipArchive();
if ($z->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    die("zip open failed\n");
}

$count = 0; $bytes = 0;
$it = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root, RecursiveDirectoryIterator::SKIP_DOTS | FilesystemIterator::UNIX_PATHS),
    RecursiveIteratorIterator::SELF_FIRST
);
foreach ($it as $info) {
    if (! $info->isFile()) continue;
    $rel = str_replace('\\', '/', substr($info->getPathname(), strlen($root) + 1));
    if (in_array($rel, $excludeExact, true)) continue;
    if (in_array(basename($rel), $excludeBasename, true)) continue;
    if (basename($rel) !== '.gitignore') {
        foreach ($excludeDirPrefixes as $p) if (str_starts_with($rel, $p)) continue 2;
    }
    $z->addFile($info->getPathname(), $rel);
    $count++;
    $bytes += $info->getSize();
}
// Include .env.production as .env inside the zip
$z->addFile($root.'/.env.production', '.env');
$count++;
$z->close();

$zipSize = filesize($zipPath);
echo "→ {$count} files (".number_format($bytes/1048576, 2)." MiB raw) → zip ".number_format($zipSize/1048576, 2)." MiB\n";

// --- Upload ZIP + extractor script
echo "connecting FTPS to {$host}…\n";
$ftp = ftp_ssl_connect($host, 21, 30);
if (! $ftp || ! ftp_login($ftp, $user, $pass)) die("ftp login failed\n");
ftp_pasv($ftp, true);
@ftp_mkdir($ftp, $remoteBase);

// Build extract.php locally and upload
$extractor = <<<'PHP'
<?php
ini_set('display_errors','1'); error_reporting(E_ALL); set_time_limit(600);
header('Content-Type: text/plain; charset=utf-8');
$token = $_GET['token'] ?? '';
$expected = __TOKEN__;
if (! hash_equals($expected, $token)) { http_response_code(403); exit("forbidden\n"); }
$zip = __DIR__.'/moe-deploy.zip';
if (! is_file($zip)) exit("zip missing: $zip\n");
$z = new ZipArchive();
if ($z->open($zip) !== true) exit("zip open failed\n");
echo "extracting ".$z->numFiles." entries…\n";
if (! $z->extractTo(__DIR__)) exit("extractTo failed\n");
$z->close();
echo "✔ extracted to ".__DIR__."\n";
if (isset($_GET['keep'])) exit("(kept zip)\n");
@unlink($zip);
@unlink(__FILE__);
echo "✔ removed zip and extractor\n";
PHP;
$extractor = str_replace('__TOKEN__', var_export($token, true), $extractor);
$tmpExt = sys_get_temp_dir().'/extract.php';
file_put_contents($tmpExt, $extractor);

$remoteZip = $remoteBase.'/moe-deploy.zip';
$remoteExt = $remoteBase.'/extract.php';

echo "uploading extract.php…\n";
if (! ftp_put($ftp, $remoteExt, $tmpExt, FTP_BINARY)) die("extract.php upload failed\n");

echo "uploading zip (".number_format($zipSize/1048576,2)." MiB)…\n";
$started = microtime(true);
if (! ftp_put($ftp, $remoteZip, $zipPath, FTP_BINARY)) die("zip upload failed\n");
$took = microtime(true) - $started;
echo sprintf("✔ uploaded in %.1fs (%.2f MiB/s)\n", $took, $zipSize/1048576/$took);
ftp_close($ftp);

@unlink($tmpExt);

echo "\nNow run these URLs in order:\n";
echo "  1) http://moe-laravel.weststar-dev.com/extract.php?token={$token}\n";
echo "  2) http://moe-laravel.weststar-dev.com/install.php?token={$token}&seed=1\n";
echo "  3) (cleanup)  http://moe-laravel.weststar-dev.com/install.php?token={$token}&delete=1\n";
