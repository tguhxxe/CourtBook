<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Court;
use App\Models\Reservation;
use App\Models\Setting;
use App\Models\User;
use App\Services\Booking\BookingService;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class BookingRulesTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;

    private Court $court;

    private BookingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->travelTo(now()->setDate(2026, 10, 7)->setTime(8, 0));
        $this->customer = User::where('role', 'customer')->first();
        $this->court = Court::where('name', 'Tennis Court A')->first();
        $this->service = app(BookingService::class);
    }

    private function book(array $change = []): Booking
    {
        return $this->service->create($this->customer, $this->court, array_merge(['date' => '2026-10-09', 'hour' => 9, 'duration' => 1], $change));
    }

    public function test_price_dp_and_rate_snapshot_are_integer_and_immutable(): void
    {
        $b = $this->book(['duration' => 3]);
        $this->assertSame(300000, $b->total);
        $this->assertSame(150000, $b->dp_amount);
        $this->court->update(['hourly_rate' => 999999]);
        Setting::find(1)->update(['dp_percent' => 25]);
        $this->assertSame(100000, $b->fresh()->hourly_rate);
        $this->assertSame(50, $b->fresh()->dp_percent);
        $this->assertSame(300000, $b->fresh()->remaining());
    }

    public function test_fractional_dp_rounds_up_in_integer_rupiah(): void
    {
        $this->court->update(['hourly_rate' => 100001]);
        Setting::find(1)->update(['dp_percent' => 33]);
        $this->assertSame(33001, $this->book()->dp_amount);
    }

    public function test_overlapping_booking_rejected_and_adjacent_booking_allowed(): void
    {
        $this->book(['duration' => 2]);
        $adjacent = $this->book(['hour' => 11]);
        $this->assertSame(3, Reservation::count());
        try {
            $this->book(['hour' => 10]);
            $this->fail('Overlap accepted');
        } catch (ValidationException $e) {
            $this->assertStringContainsString('terisi', $e->getMessage());
        }$this->assertSame(2, Booking::count());
    }

    public function test_same_hour_on_different_courts_is_allowed(): void
    {
        $this->book();
        $other = Court::where('name', 'Tennis Court B')->first();
        $this->service->create($this->customer, $other, ['date' => '2026-10-09', 'hour' => 9, 'duration' => 1]);
        $this->assertSame(2, Booking::count());
    }

    public static function invalidInputs(): array
    {
        return [['duration', 0], ['duration', 5], ['hour', 6], ['hour', 23], ['date', '2026-11-07'], ['date', '2026-10-06']];
    }

    #[DataProvider('invalidInputs')]
    public function test_booking_boundaries_rejected(string $key, mixed $value): void
    {
        $this->expectException(ValidationException::class);
        $this->book([$key => $value]);
    }

    public function test_close_time_and_max_advance_boundary_allowed(): void
    {
        $b = $this->book(['date' => '2026-11-06', 'hour' => 19, 'duration' => 4]);
        $this->assertSame('23:00', $b->ends_at->format('H:i'));
    }

    public function test_duration_crossing_close_rejected(): void
    {
        $this->expectException(ValidationException::class);
        $this->book(['hour' => 21, 'duration' => 3]);
    }

    public function test_two_hour_lead_boundary(): void
    {
        $b = $this->book(['date' => '2026-10-07', 'hour' => 10]);
        $this->assertNotNull($b->id);
        $this->expectException(ValidationException::class);
        $this->book(['date' => '2026-10-07', 'hour' => 9]);
    }

    public function test_maintenance_prevents_booking_and_overlap(): void
    {
        $this->service->maintenance(['court_id' => $this->court->id, 'starts_at' => '2026-10-09T09:00', 'ends_at' => '2026-10-09T11:00', 'reason' => 'Maintenance demo']);
        $this->expectException(ValidationException::class);
        $this->book(['hour' => 8, 'duration' => 2]);
    }

    public function test_maintenance_cannot_replace_existing_booking(): void
    {
        $this->book();
        $this->expectException(ValidationException::class);
        $this->service->maintenance(['court_id' => $this->court->id, 'starts_at' => '2026-10-09T09:00', 'ends_at' => '2026-10-09T11:00', 'reason' => 'Maintenance']);
    }

    public function test_maintenance_requires_whole_hours(): void
    {
        $this->expectException(ValidationException::class);
        $this->service->maintenance(['court_id' => $this->court->id, 'starts_at' => '2026-10-09T09:30', 'ends_at' => '2026-10-09T11:00', 'reason' => 'Maintenance']);
    }

    public function test_inactive_court_rejects_booking(): void
    {
        $this->court->update(['active' => false]);
        $this->expectException(ValidationException::class);
        $this->book();
    }

    public function test_hold_expires_at_exact_deadline_and_releases_slot(): void
    {
        $b = $this->book();
        $this->travel(15)->minutes();
        $this->assertSame(1, $this->service->expire());
        $this->assertSame('expired', $b->fresh()->status);
        $this->assertSame(0, Reservation::count());
        $this->book();
        $this->assertSame(1, Reservation::count());
    }

    public function test_unpaid_cancellation_and_repeated_cancel(): void
    {
        $b = $this->book();
        $this->service->cancel($b);
        $this->assertSame('cancelled', $b->fresh()->status);
        $this->assertSame(0, Reservation::count());
        $this->expectException(ValidationException::class);
        $this->service->cancel($b);
    }

    public function test_paid_cancellation_at_24_hours_creates_single_refund(): void
    {
        $b = $this->book();
        $b->update(['status' => 'confirmed', 'paid_amount' => 50000, 'payment_status' => 'partial']);
        $this->travelTo($b->starts_at->copy()->subHours(24));
        $this->service->cancel($b);
        $this->assertDatabaseHas('refund_requests', ['booking_id' => $b->id, 'amount' => 50000, 'status' => 'requested']);
        $this->assertSame('partial', $b->fresh()->payment_status);
    }

    public function test_paid_cancellation_inside_24_hours_rejected(): void
    {
        $b = $this->book();
        $b->update(['status' => 'confirmed', 'paid_amount' => 50000, 'payment_status' => 'partial']);
        $this->travelTo($b->starts_at->copy()->subHours(23));
        $this->expectException(ValidationException::class);
        $this->service->cancel($b);
    }

    public function test_balance_deadline_cancels_partial_and_forfeits_dp(): void
    {
        $b = $this->book();
        $b->update(['status' => 'confirmed', 'paid_amount' => 50000, 'payment_status' => 'partial']);
        $this->travelTo($b->balance_due_at);
        $this->artisan('courtbook:expire')->assertSuccessful();
        $this->assertSame('cancelled', $b->fresh()->status);
        $this->assertSame(50000, $b->fresh()->paid_amount);
        $this->assertSame(0, $b->refunds()->count());
        $this->assertSame(0, Reservation::count());
    }

    public function test_fully_paid_booking_survives_balance_deadline(): void
    {
        $b = $this->book();
        $b->update(['status' => 'confirmed', 'paid_amount' => 100000, 'payment_status' => 'paid']);
        $this->travelTo($b->balance_due_at);
        $this->service->expire();
        $this->assertSame('confirmed', $b->fresh()->status);
    }

    public function test_reservation_database_unique_constraint(): void
    {
        $b = $this->book();
        $this->expectException(QueryException::class);
        Reservation::create(['court_id' => $this->court->id, 'starts_at' => $b->starts_at, 'booking_id' => $b->id]);
    }
}
