<?php

namespace Tests\Unit;

use App\Models\Booking;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class BookingCancellationTest extends TestCase
{
    public static function cases(): array
    {
        return [['held', 0, 1, true], ['confirmed', 50000, 24, true], ['confirmed', 50000, 23, false], ['expired', 0, 48, false], ['cancelled', 50000, 48, false]];
    }

    #[DataProvider('cases')]
    public function test_cancellation_decision_branches(string $status, int $paid, int $hours, bool $expected): void
    {
        $this->travelTo(now()->startOfHour());
        $b = new Booking(['status' => $status, 'paid_amount' => $paid, 'starts_at' => now()->addHours($hours), 'total' => 100000]);
        $this->assertSame($expected, $b->canCancel());
        $this->assertSame(100000 - $paid, $b->remaining());
    }
}
