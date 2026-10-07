<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Reservation;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class ConcurrencyTest extends TestCase
{
    public function test_two_processes_cannot_book_same_slot_in_file_sqlite(): void
    {
        $dir = storage_path('framework/testing');
        if (! is_dir($dir)) {
            mkdir($dir, 0777, true);
        }$file = $dir.'/concurrency-'.bin2hex(random_bytes(6)).'.sqlite';
        touch($file);
        $previous = config('database.connections.sqlite.database');
        config(['database.connections.sqlite.database' => $file]);
        DB::purge('sqlite');
        try {
            $this->artisan('migrate', ['--force' => true])->assertSuccessful();
            $this->seed();
            $start = (string) (microtime(true) + 1.5);
            $env = ['APP_ENV' => 'testing', 'DB_DATABASE' => $file, 'DB_CONNECTION' => 'sqlite'];
            $a = new Process([PHP_BINARY, base_path('tests/Support/booking-worker.php'), $file, $start], base_path(), $env);
            $b = new Process([PHP_BINARY, base_path('tests/Support/booking-worker.php'), $file, $start], base_path(), $env);
            $a->start();
            $b->start();
            $a->wait();
            $b->wait();
            $this->assertSame(0, $a->getExitCode(), $a->getErrorOutput());
            $this->assertSame(0, $b->getExitCode(), $b->getErrorOutput());
            $out = [$a->getOutput(), $b->getOutput()];
            sort($out);
            $this->assertSame(['BOOKED', 'CONFLICT'], $out);
            $this->assertSame(1, Booking::count());
            $this->assertSame(2, Reservation::count());
        } finally {
            DB::disconnect('sqlite');
            config(['database.connections.sqlite.database' => $previous]);
            DB::purge('sqlite');
            foreach ([$file, $file.'-wal', $file.'-shm'] as $tmp) {
                if (file_exists($tmp)) {
                    unlink($tmp);
                }
            }
        }
    }
}
