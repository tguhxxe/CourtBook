<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));
require __DIR__.'/../vendor/autoload.php';

/** @var Application $app */
$app = require __DIR__.'/../bootstrap/app.php';

// Serverless deployments provide only temporary writable storage.
$storage = sys_get_temp_dir().'/courtbook-vercel';
foreach (['framework/cache/data', 'framework/sessions', 'framework/views', 'logs'] as $directory) {
    $path = $storage.'/'.$directory;
    if (! is_dir($path) && ! mkdir($path, 0700, true) && ! is_dir($path)) {
        throw new RuntimeException('Temporary storage unavailable.');
    }
}
$app->useStoragePath($storage);
$app->handleRequest(Request::capture());
