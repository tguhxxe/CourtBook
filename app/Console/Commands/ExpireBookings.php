<?php

namespace App\Console\Commands;

use App\Services\Booking\BookingService;
use Illuminate\Console\Command;

class ExpireBookings extends Command
{
    protected $signature = 'courtbook:expire';

    protected $description = 'Lepaskan reservasi kedaluwarsa dan booking DP yang tidak dilunasi';

    public function handle(BookingService $service): int
    {
        $this->info($service->expire().' booking diperbarui.');

        return self::SUCCESS;
    }
}
