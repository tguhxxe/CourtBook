<?php

namespace App\Services\Payments;

use App\Models\Booking;
use App\Models\PaymentAttempt;
use App\Models\RefundRequest;
use App\Models\Reservation;
use App\Services\Booking\BookingService;
use Illuminate\Support\Str;

class PaymentService
{
    public function __construct(private BookingService $bookings, private MidtransGateway $gateway) {}

    public function start(Booking $booking, string $kind): PaymentAttempt
    {
        if (! $this->gateway->configured()) {
            $this->bookings->reject('Pembayaran Sandbox belum tersedia. Hubungi pengelola untuk informasi.');
        }
        $attempt = $this->bookings->atomic(function () use ($booking, $kind) {
            $this->bookings->expireLocked();
            $b = $booking->fresh();
            if (! in_array($b->status, ['held', 'confirmed']) || ! $b->remaining()) {
                $this->bookings->reject('Booking tidak dapat dibayar.');
            }
            if (now()->gte($b->balance_due_at)) {
                $this->bookings->reject('Batas pelunasan telah terlewati.');
            }
            $active = PaymentAttempt::where('active_booking_id', $b->id)->first();
            if ($active) {
                if ($active->kind !== $kind || ! $active->snap_token || now()->gte($active->expires_at)) {
                    $this->bookings->reject('Masih ada percobaan pembayaran. Periksa status terlebih dahulu; jangan membayar dua kali.');
                }

                return $active;
            }
            if ($kind === 'dp' && $b->paid_amount) {
                $this->bookings->reject('DP sudah dibayar. Pilih pelunasan.');
            }
            if ($kind === 'balance' && ! $b->paid_amount) {
                $this->bookings->reject('Belum ada DP untuk dilunasi.');
            }
            $amount = $kind === 'dp' ? $b->dp_amount : $b->remaining();

            return PaymentAttempt::create(['booking_id' => $b->id, 'active_booking_id' => $b->id, 'order_id' => 'CBPAY-'.Str::uuid(), 'amount' => $amount, 'kind' => $kind, 'expires_at' => min($b->balance_due_at, $b->status === 'held' ? $b->hold_expires_at : now()->addMinutes(15))]);
        });
        if ($attempt->snap_token) {
            return $attempt;
        }
        try {
            $token = $this->gateway->snap($attempt);
            $this->bookings->atomic(function () use ($attempt, $token) {
                $a = $attempt->fresh();
                $a->update(['snap_token' => $token, 'status' => $a->status === 'creating' ? 'pending' : $a->status]);
            });
        } catch (\Throwable $e) {
            $this->bookings->atomic(function () use ($attempt) {
                $a = $attempt->fresh();
                if ($a->status === 'creating') {
                    $a->update(['status' => 'uncertain']);
                }
            });
            $this->bookings->reject('Pembayaran belum dapat dibuka. Periksa status sebelum mencoba kembali.');
        }

        return $attempt->fresh();
    }

    public function notification(array $payload): void
    {
        $key = config('courtbook.midtrans.server_key');
        if (! $key) {
            abort(503, 'Pembayaran belum dikonfigurasi.');
        }
        foreach (['order_id', 'status_code', 'gross_amount', 'signature_key'] as $field) {
            if (! isset($payload[$field]) || ! is_string($payload[$field])) {
                abort(400, 'Notifikasi tidak lengkap.');
            }
        }
        $expected = hash('sha512', $payload['order_id'].$payload['status_code'].$payload['gross_amount'].$key);
        if (! hash_equals($expected, $payload['signature_key'])) {
            abort(403, 'Signature tidak sah.');
        }
        $attempt = PaymentAttempt::where('order_id', $payload['order_id'])->firstOrFail();
        $this->validateAmount($payload, $attempt);
        // GET Status is authoritative even if webhook delivery is stale/out of order.
        $this->apply($attempt, $this->gateway->status($attempt->order_id));
    }

    public function reconcile(PaymentAttempt $attempt): void
    {
        if (! $this->gateway->configured()) {
            $this->bookings->reject('Pembayaran Sandbox belum tersedia.');
        } $this->apply($attempt, $this->gateway->status($attempt->order_id));
    }

    private function validateAmount(array $data, PaymentAttempt $a): void
    {
        $gross = (string) ($data['gross_amount'] ?? '');
        if (($data['order_id'] ?? null) !== $a->order_id || ! preg_match('/^\d+(?:\.00)?$/D', $gross) || (int) $gross !== $a->amount || (isset($data['currency']) && $data['currency'] !== 'IDR')) {
            abort(422, 'Identitas atau nominal transaksi tidak sesuai.');
        }
        $merchant = config('courtbook.midtrans.merchant_id');
        if ($merchant && ($data['merchant_id'] ?? null) !== $merchant) {
            abort(422, 'Merchant tidak sesuai.');
        }
    }

    private function apply(PaymentAttempt $attempt, array $data): void
    {
        $this->validateAmount($data, $attempt);
        $this->bookings->atomic(function () use ($attempt, $data) {
            $this->bookings->expireLocked();
            $a = $attempt->fresh();
            $b = $a->booking;
            $state = $data['transaction_status'] ?? '';
            $success = $state === 'settlement' || ($state === 'capture' && ($data['fraud_status'] ?? '') === 'accept');
            if ($success) {
                if (! is_string($data['transaction_id'] ?? null) || ! $data['transaction_id']) {
                    abort(422, 'Identitas transaksi tidak lengkap.');
                }
                if ($a->provider_transaction_id && $a->provider_transaction_id !== $data['transaction_id']) {
                    abort(422, 'Identitas transaksi berubah.');
                }
                if (PaymentAttempt::where('provider_transaction_id', $data['transaction_id'])->where('id', '!=', $a->id)->exists()) {
                    abort(422, 'Identitas transaksi sudah digunakan.');
                }
                if ($a->credited_at) {
                    return;
                }
                $a->update(['status' => 'paid', 'active_booking_id' => null, 'provider_transaction_id' => $data['transaction_id'], 'verified_at' => now(), 'credited_at' => now()]);
                // Enforce final payment cutoff even for initial full payment near the lead-time boundary.
                if ($b->status === 'held' && now()->gte($b->balance_due_at)) {
                    $b->update(['status' => 'cancelled', 'cancellation_reason' => 'Pembayaran diterima setelah batas 2 jam sebelum jadwal; perlu refund manual.']);
                    Reservation::where('booking_id', $b->id)->delete();
                }
                // A cancelled/expired booking NEVER regains its released slots.
                if (! in_array($b->status, ['held', 'confirmed']) || $a->amount > $b->remaining()) {
                    RefundRequest::firstOrCreate(['reference' => 'late-'.$a->id], ['booking_id' => $b->id, 'payment_attempt_id' => $a->id, 'amount' => $a->amount, 'reason' => 'Pembayaran terlambat atau melebihi sisa tagihan; perlu penanganan manual.']);

                    return;
                }
                $paid = $b->paid_amount + $a->amount;
                $b->update(['paid_amount' => $paid, 'payment_status' => $paid === $b->total ? 'paid' : 'partial', 'status' => 'confirmed']);

                return;
            }
            if (in_array($state, ['refund', 'partial_refund', 'chargeback', 'partial_chargeback'])) {
                RefundRequest::firstOrCreate(['reference' => 'provider-'.$a->id.'-'.$state], ['booking_id' => $b->id, 'payment_attempt_id' => $a->id, 'amount' => $a->amount, 'reason' => 'Status kanal '.$state.'; rekonsiliasi manual diperlukan.']);

                return;
            }
            if ($a->credited_at || in_array($a->status, ['paid', 'deny', 'cancel', 'expire', 'failure'])) {
                return;
            }
            if (in_array($state, ['deny', 'cancel', 'expire', 'failure'])) {
                $a->update(['status' => $state, 'active_booking_id' => null, 'verified_at' => now()]);
            } elseif (in_array($state, ['pending', 'capture'])) {
                $a->update(['status' => 'pending', 'verified_at' => now()]);
            }
        });
    }
}
