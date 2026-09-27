<?php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// Prepare writable storage directory in /tmp for Vercel Serverless environment
$storageDirs = [
    '/tmp/storage/framework/views',
    '/tmp/storage/framework/cache',
    '/tmp/storage/framework/cache/data',
    '/tmp/storage/framework/sessions',
    '/tmp/storage/logs',
    '/tmp/bootstrap/cache',
];

foreach ($storageDirs as $dir) {
    if (! is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
}

// Forward execution to Laravel's public entrypoint
try {
    require __DIR__.'/../public/index.php';
} catch (Throwable $e) {
    http_response_code(500);
    echo '<div style="font-family:sans-serif;padding:30px;max-width:800px;margin:auto;">';
    echo '<h1 style="color:#e53e3e;">Serverless Error</h1>';
    echo '<p style="font-size:18px;"><strong>'.htmlspecialchars(get_class($e)).':</strong> '.htmlspecialchars($e->getMessage()).'</p>';
    echo '<p><strong>File:</strong> '.htmlspecialchars($e->getFile()).':'.$e->getLine().'</p>';
    echo '<pre style="background:#edf2f7;padding:15px;border-radius:8px;overflow-x:auto;">'.htmlspecialchars($e->getTraceAsString()).'</pre>';
    echo '</div>';
}
