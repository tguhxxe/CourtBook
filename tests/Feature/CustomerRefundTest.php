<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Court;
use App\Models\RefundRequest;
use App\Models\Reservation;
use App\Models\User;
use App\Services\Booking\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerRefundTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;

    private Booking $booking;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->travelTo(now()->setDate(2026, 10, 7)->setTime(8, 0));
        $this->customer = User::where('role', 'customer')->firstOrFail();
        $this->booking = app(BookingService::class)->create($this->customer, Court::first(), ['date' => '2026-10-09', 'hour' => 9, 'duration' => 1]);
        $this->booking->update(['status' => 'confirmed', 'payment_status' => 'partial', 'paid_amount' => 50000]);
    }

    private function submit(array $data = [])
    {
        return $this->post('/bookings/'.$this->booking->id.'/refund', $data + ['reason' => 'Tidak dapat hadir pada jadwal tersebut.']);
    }

    public function test_customer_refund_cancels_releases_and_uses_server_amount(): void
    {
        $this->actingAs($this->customer)->get('/bookings/'.$this->booking->id)->assertSee('Ajukan refund');
        $this->submit(['amount' => 999999, 'status' => 'processed'])->assertRedirect(route('refunds.index'));
        $this->assertSame('cancelled', $this->booking->fresh()->status);
        $this->assertSame(0, Reservation::where('booking_id', $this->booking->id)->count());
        $this->assertDatabaseHas('refund_requests', ['booking_id' => $this->booking->id, 'amount' => 50000, 'status' => 'requested', 'reason' => 'Tidak dapat hadir pada jadwal tersebut.']);
        $this->submit()->assertSessionHasErrors('booking');
        $this->assertSame(1, RefundRequest::count());
        $admin = User::where('role', 'admin')->firstOrFail();
        $refund = RefundRequest::firstOrFail();
        $this->actingAs($admin)->get('/admin/refunds')->assertSee('Tidak dapat hadir pada jadwal tersebut.');
        $this->put('/admin/refunds/'.$refund->id, ['status' => 'reviewing', 'admin_notes' => 'Permintaan sedang ditinjau.'])->assertSessionHasNoErrors();
        $this->actingAs($this->customer)->get('/refunds')->assertSee('Ditinjau admin')->assertSee('Permintaan sedang ditinjau.');
    }

    public function test_customer_cannot_refund_or_list_other_customer_booking(): void
    {
        $other = User::factory()->create(['role' => 'customer']);
        $this->actingAs($other);
        $this->submit()->assertForbidden();
        RefundRequest::create(['booking_id' => $this->booking->id, 'reference' => 'private-refund', 'amount' => 50000, 'reason' => 'Alasan pribadi pelanggan lain.']);
        $this->get('/refunds')->assertOk()->assertDontSee('Alasan pribadi pelanggan lain.')->assertSee('Belum ada pengajuan refund');
    }

    public function test_guest_and_admin_cannot_use_customer_refund(): void
    {
        $this->get('/refunds')->assertRedirect(route('login'));
        $this->submit()->assertRedirect(route('login'));
        $this->actingAs(User::where('role', 'admin')->firstOrFail());
        $this->get('/refunds')->assertForbidden();
        $this->submit()->assertForbidden();
    }

    public function test_refund_requires_reason_and_preserves_booking_on_invalid_input(): void
    {
        $this->actingAs($this->customer);
        foreach (['', 'abc', str_repeat('a', 201)] as $reason) {
            $this->submit(['reason' => $reason])->assertSessionHasErrors('reason');
        }
        $this->assertSame('confirmed', $this->booking->fresh()->status);
        $this->assertSame(0, RefundRequest::count());
    }

    public function test_unpaid_booking_cannot_request_refund(): void
    {
        $this->booking->update(['status' => 'held', 'payment_status' => 'unpaid', 'paid_amount' => 0]);
        $this->actingAs($this->customer);
        $this->submit()->assertSessionHasErrors('booking');
        $this->assertSame('held', $this->booking->fresh()->status);
        $this->assertSame(0, RefundRequest::count());
    }

    public function test_paid_refund_allowed_exactly_at_24_hours_but_not_after(): void
    {
        $this->actingAs($this->customer);
        $this->travelTo($this->booking->starts_at->copy()->subHours(24)->addSecond());
        $this->submit()->assertSessionHasErrors('booking');
        $this->assertSame(0, RefundRequest::count());
        $this->travelTo($this->booking->starts_at->copy()->subHours(24));
        $this->submit()->assertRedirect(route('refunds.index'));
        $this->assertSame(1, RefundRequest::count());
    }

    public function test_late_payment_refunds_and_completed_admin_notes_are_visible(): void
    {
        $this->booking->update(['status' => 'expired']);
        RefundRequest::create(['booking_id' => $this->booking->id, 'reference' => 'late-test', 'amount' => 50000, 'reason' => 'Pembayaran terlambat.', 'status' => 'processed', 'admin_notes' => '<script>alert(1)</script> Catatan simulasi.']);
        $this->actingAs($this->customer)->get('/refunds')->assertOk()->assertSee('Pembayaran terlambat.')->assertSee('Penanganan selesai')->assertSee('&lt;script&gt;', false)->assertDontSee('<script>alert(1)</script>', false);
        $this->submit()->assertSessionHasErrors('booking');
        $this->assertSame(1, RefundRequest::count());
    }
}
