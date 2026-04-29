<?php
// Shared-host shim: forwards every request from the site root into Laravel's public/.
// The document root on this shared host is /public_html/moe-laravel.weststar-dev.com/,
// NOT the Laravel public/ folder.
require __DIR__.'/public/index.php';
