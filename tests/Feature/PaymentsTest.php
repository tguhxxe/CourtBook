<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Court;
use App\Models\PaymentAttempt;
use App\Models\RefundRequest;
use App\Models\Reservation;
use App\Models\User;
use App\Services\Booking\BookingService;
use App\Services\Payments\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Factory;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PaymentsTest extends TestCase
{
    use RefreshDatabase;

    private Booking $booking;

    private User $customer;

    private PaymentService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->travelTo(now()->setDate(2026, 10, 7)->setTime(8, 0));
        $this->customer = User::where('role', 'customer')->first();
        $this->booking = app(BookingService::class)->create($this->customer, Court::first(), ['date' => '2026-10-09', 'hour' => 9, 'duration' => 2]);
        $this->service = app(PaymentService::class);
        config(['courtbook.midtrans.server_key' => 'test-server', 'courtbook.midtrans.client_key' => 'test-client']);
        Http::preventStrayRequests();
    }

    private function start(string $kind = 'dp'): PaymentAttempt
    {
        Http::swap(new Factory);
        Http::preventStrayRequests();
        Http::fake(['*snap/v1/transactions' => Http::response(['token' => 'fake-snap-token'])]);

        return $this->service->start($this->booking, $kind);
    }

    private function data(PaymentAttempt $a, string $status = 'settlement', array $change = []): array
    {
        $d = array_merge(['order_id' => $a->order_id, 'status_code' => '200', 'gross_amount' => $a->amount.'.00', 'transaction_status' => $status, 'transaction_id' => 'provider-'.$a->id, 'fraud_status' => 'accept', 'currency' => 'IDR'], $change);
        $d['signature_key'] = hash('sha512', $d['order_id'].$d['status_code'].$d['gross_amount'].'test-server');

        return $d;
    }

    private function notify(PaymentAttempt $a, string $status = 'settlement', array $change = []): void
    {
        $data = $this->data($a, $status, $change);
        Http::swap(new Factory);
        Http::preventStrayRequests();
        Http::fake(['*/status' => Http::response($data)]);
        $this->service->notification($data);
    }

    public function test_snap_uses_unique_order_backend_auth_and_reuses_active_attempt(): void
    {
        $a = $this->start();
        $same = $this->service->start($this->booking, 'dp');
        $this->assertSame($a->id, $same->id);
        $this->assertSame(1, PaymentAttempt::count());
        Http::assertSent(fn ($r) => $r['transaction_details']['gross_amount'] === 100000 && $r['transaction_details']['order_id'] === $a->order_id && $r->hasHeader('Authorization'));
    }

    public function test_initial_dp_confirms_booking_and_balance_settles_once(): void
    {
        $a = $this->start();
        $this->notify($a);
        $this->assertSame('confirmed', $this->booking->fresh()->status);
        $this->assertSame('partial', $this->booking->fresh()->payment_status);
        $this->assertSame(100000, $this->booking->fresh()->remaining());
        $balance = $this->start('balance');
        $this->assertNotSame($a->order_id, $balance->order_id);
        $this->notify($balance);
        $this->notify($balance);
        $this->assertSame(200000, $this->booking->fresh()->paid_amount);
        $this->assertSame('paid', $this->booking->fresh()->payment_status);
    }

    public function test_full_payment_and_duplicate_and_stale_pending(): void
    {
        $a = $this->start('full');
        $this->notify($a);
        $this->notify($a);
        $this->notify($a, 'pending');
        $this->assertSame(200000, $this->booking->fresh()->paid_amount);
        $this->assertSame('paid', $a->fresh()->status);
    }

    public function test_invalid_signature_rejected_without_network_or_credit(): void
    {
        $a = $this->start();
        $data = $this->data($a);
        $data['signature_key'] = 'bad';
        $this->postJson('/midtrans/notification', $data)->assertForbidden();
        $this->assertSame(0, $this->booking->fresh()->paid_amount);
    }

    public function test_mismatched_amount_rejected(): void
    {
        $a = $this->start();
        $data = $this->data($a, 'settlement', ['gross_amount' => '1.00']);
        $this->postJson('/midtrans/notification', $data)->assertStatus(422);
        $this->assertSame(0, $this->booking->fresh()->paid_amount);
    }

    public function test_server_status_mismatch_rejected(): void
    {
        $a = $this->start();
        $data = $this->data($a);
        Http::fake(['*/status' => Http::response($this->data($a, 'settlement', ['order_id' => 'other-order']))]);
        $this->postJson('/midtrans/notification', $data)->assertStatus(422);
        $this->assertSame(0, $this->booking->fresh()->paid_amount);
    }

    public function test_capture_fraud_challenge_does_not_confirm(): void
    {
        $a = $this->start();
        $this->notify($a, 'capture', ['fraud_status' => 'challenge']);
        $this->assertSame('held', $this->booking->fresh()->status);
        $this->assertSame(0, $this->booking->fresh()->paid_amount);
        $this->notify($a, 'capture');
        $this->assertSame('confirmed', $this->booking->fresh()->status);
    }

    public function test_stale_webhook_uses_current_server_status(): void
    {
        $a = $this->start();
        $data = $this->data($a, 'pending');
        Http::fake(['*/status' => Http::response($this->data($a))]);
        $this->postJson('/midtrans/notification', $data)->assertOk();
        $this->assertSame('confirmed', $this->booking->fresh()->status);
    }

    public function test_late_payment_after_expiry_refunds_and_does_not_steal_new_slot(): void
    {
        $a = $this->start();
        $this->travel(16)->minutes();
        app(BookingService::class)->expire();
        $replacement = app(BookingService::class)->create($this->customer, $this->booking->court, ['date' => '2026-10-09', 'hour' => 9, 'duration' => 2]);
        $this->notify($a);
        $this->notify($a);
        $this->assertSame('expired', $this->booking->fresh()->status);
        $this->assertSame(0, $this->booking->fresh()->paid_amount);
        $this->assertSame(1, RefundRequest::count());
        $this->assertSame(2, Reservation::where('booking_id', $replacement->id)->count());
    }

    public function test_payment_after_unpaid_cancellation_is_manual_refund(): void
    {
        $a = $this->start();
        app(BookingService::class)->cancel($this->booking);
        $this->notify($a);
        $this->assertSame('cancelled', $this->booking->fresh()->status);
        $this->assertDatabaseHas('refund_requests', ['amount' => 100000, 'payment_attempt_id' => $a->id]);
    }

    public function test_new_payment_blocked_while_other_kind_pending(): void
    {
        $this->start();
        $this->expectException(ValidationException::class);
        $this->service->start($this->booking, 'full');
    }

    public function test_second_dp_and_payment_above_remaining_prevented(): void
    {
        $a = $this->start();
        $this->notify($a);
        $this->expectException(ValidationException::class);
        $this->service->start($this->booking, 'dp');
    }

    public function test_fully_paid_booking_cannot_pay_again(): void
    {
        $a = $this->start('full');
        $this->notify($a);
        $this->expectException(ValidationException::class);
        $this->service->start($this->booking, 'full');
    }

    public function test_failed_attempt_can_retry_with_new_order_and_no_out_of_order_regression(): void
    {
        $a = $this->start();
        $this->notify($a, 'expire');
        $this->notify($a, 'pending');
        $this->assertSame('expire', $a->fresh()->status);
        $new = $this->start();
        $this->assertNotSame($a->order_id, $new->order_id);
    }

    public function test_old_success_after_new_attempt_paid_is_overpayment_refund(): void
    {
        $a = $this->start('full');
        $this->notify($a, 'expire');
        $new = $this->start('full');
        $this->notify($new);
        $this->notify($a);
        $this->assertSame(200000, $this->booking->fresh()->paid_amount);
        $this->assertDatabaseHas('refund_requests', ['payment_attempt_id' => $a->id, 'amount' => 200000]);
    }

    public function test_unconfigured_midtrans_does_not_fake_payment(): void
    {
        config(['courtbook.midtrans.server_key' => null]);
        $this->actingAs($this->customer)->get('/bookings/'.$this->booking->id)->assertOk()->assertSee('Pembayaran Sandbox belum tersedia');
        $this->expectException(ValidationException::class);
        $this->service->start($this->booking, 'dp');
    }

    public function test_uncertain_gateway_failure_keeps_unique_attempt(): void
    {
        Http::fake(['*snap/v1/transactions' => Http::response([], 500)]);
        try {
            $this->service->start($this->booking, 'dp');
            $this->fail();
        } catch (ValidationException $e) {
            $this->assertDatabaseHas('payment_attempts', ['status' => 'uncertain', 'active_booking_id' => $this->booking->id]);
        }$this->expectException(ValidationException::class);
        $this->service->start($this->booking, 'dp');
    }

    public function test_pending_reconcile_does_not_trust_browser_or_confirm(): void
    {
        $a = $this->start();
        Http::fake(['*/status' => Http::response($this->data($a, 'pending'))]);
        $this->actingAs($this->customer)->post('/payments/'.$a->id.'/reconcile')->assertRedirect();
        $this->assertSame('held', $this->booking->fresh()->status);
    }

    public function test_balance_payment_after_deadline_becomes_refund(): void
    {
        $dp = $this->start();
        $this->notify($dp);
        $balance = $this->start('balance');
        $this->travelTo($this->booking->balance_due_at);
        $this->notify($balance);
        $this->assertSame('cancelled', $this->booking->fresh()->status);
        $this->assertSame(100000, $this->booking->fresh()->paid_amount);
        $this->assertDatabaseHas('refund_requests', ['payment_attempt_id' => $balance->id]);
    }

    public function test_merchant_and_currency_checked(): void
    {
        $a = $this->start();
        config(['courtbook.midtrans.merchant_id' => 'demo-merchant']);
        $data = $this->data($a, 'settlement', ['merchant_id' => 'other']);
        $this->postJson('/midtrans/notification', $data)->assertStatus(422);
    }

    public function test_initial_payment_cannot_start_at_balance_cutoff(): void
    {
        $this->booking->update(['balance_due_at' => now()]);
        $this->expectException(ValidationException::class);
        $this->service->start($this->booking, 'full');
    }

    public function test_initial_full_success_after_cutoff_is_refund_without_confirming(): void
    {
        $a = $this->start('full');
        $this->booking->update(['balance_due_at' => now()->addMinutes(1)]);
        $this->travel(2)->minutes();
        $this->notify($a);
        $this->assertSame('cancelled', $this->booking->fresh()->status);
        $this->assertSame(0, $this->booking->fresh()->paid_amount);
        $this->assertSame(0, Reservation::count());
        $this->assertDatabaseHas('refund_requests', ['payment_attempt_id' => $a->id, 'amount' => 200000]);
    }

    public function test_provider_transaction_id_cannot_be_reused_for_other_order(): void
    {
        $a = $this->start();
        $this->notify($a);
        $balance = $this->start('balance');
        $data = $this->data($balance, 'settlement', ['transaction_id' => 'provider-'.$a->id]);
        Http::swap(new Factory);
        Http::preventStrayRequests();
        Http::fake(['*/status' => Http::response($data)]);
        $this->postJson('/midtrans/notification', $data)->assertStatus(422);
        $this->assertSame(100000, $this->booking->fresh()->paid_amount);
    }

    public function test_fractional_gross_amount_and_missing_provider_id_are_rejected(): void
    {
        $a = $this->start();
        $data = $this->data($a, 'settlement', ['gross_amount' => '100000.50']);
        $this->postJson('/midtrans/notification', $data)->assertStatus(422);
        $data = $this->data($a);
        $server = $data;
        unset($server['transaction_id']);
        Http::swap(new Factory);
        Http::preventStrayRequests();
        Http::fake(['*/status' => Http::response($server)]);
        $this->postJson('/midtrans/notification', $data)->assertStatus(422);
        $this->assertSame(0, $this->booking->fresh()->paid_amount);
    }
}
