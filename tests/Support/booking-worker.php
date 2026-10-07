<?php

use App\Models\Court;
use App\Models\User;
use App\Services\Booking\BookingService;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
config(['database.connections.sqlite.database' => $argv[1]]);
DB::purge('sqlite');
while (microtime(true) < (float) $argv[2]) {
    usleep(1000);
}
try {
    app(BookingService::class)->create(User::where('role', 'customer')->first(), Court::first(), ['date' => now()->addDays(2)->toDateString(), 'hour' => 12, 'duration' => 2]);
    echo 'BOOKED';
} catch (ValidationException $e) {
    echo 'CONFLICT';
}
