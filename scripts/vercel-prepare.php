<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Artisan;

if (getenv('VERCEL') !== '1') {
    fwrite(STDERR, "Script ini hanya untuk build Vercel.\n");
    exit(1);
}
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

// Preview builds never mutate the production database.
if (getenv('VERCEL_ENV') !== 'production') {
    echo "Preview: database setup dilewati.\n";
    exit(0);
}
if (config('database.default') !== 'pgsql' || ! config('database.connections.pgsql.url') || ! config('app.key') || ! str_starts_with(config('app.url'), 'https://')) {
    fwrite(STDERR, "Isi DB_CONNECTION=pgsql, DB_URL, APP_KEY dan APP_URL HTTPS pada Environment Vercel.\n");
    exit(1);
}
foreach ([['migrate', ['--force' => true]], ['db:seed', ['--force' => true]], ['courtbook:bootstrap-admin', []]] as [$command, $options]) {
    $result = Artisan::call($command, $options + ['--no-interaction' => true]);
    // Database exceptions may contain connection details. Let Vercel retain errors privately.
    echo Artisan::output();
    if ($result !== 0) {
        exit($result);
    }
}
