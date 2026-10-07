<?php

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentRequest;
use App\Models\Booking;
use App\Models\PaymentAttempt;
use App\Services\Payments\PaymentService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class PaymentController extends Controller
{
    public function store(PaymentRequest $r, Booking $booking, PaymentService $s)
    {
        Gate::authorize('pay', $booking);
        $attempt = $s->start($booking, $r->kind);

        return view('customer.pay', compact('attempt', 'booking'));
    }

    public function reconcile(Request $request, PaymentAttempt $payment, PaymentService $s)
    {
        Gate::authorize('view', $payment->booking);
        try {
            if (! $request->expectsJson() || ! $payment->credited_at) {
                $s->reconcile($payment);
            }
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Status belum dapat diperiksa. Coba lagi; jangan membayar ulang.'], 503);
            }

            return back()->withErrors(['payment' => 'Status belum dapat diperiksa. Coba kembali; jangan membuat pembayaran kedua.']);
        }

        $payment->refresh();
        $booking = $payment->booking()->firstOrFail();
        $review = $booking->refunds()->where('payment_attempt_id', $payment->id)->exists();
        $paid = $payment->status === 'paid';
        $message = $paid
            ? ($review || $booking->status !== 'confirmed'
                ? 'Pembayaran diterima. Booking tidak dikonfirmasi; lihat penanganan refund atau hubungi pengelola.'
                : ($booking->payment_status === 'paid' ? 'Pembayaran berhasil. Booking sudah lunas.' : 'Pembayaran DP berhasil. Booking dikonfirmasi; silakan lunasi sisa tagihan sebelum batas waktu.'))
            : (in_array($payment->status, ['deny', 'cancel', 'expire', 'failure'])
                ? 'Pembayaran tidak berhasil atau sudah kedaluwarsa. Periksa detail booking sebelum mencoba kembali.'
                : 'Pembayaran masih menunggu penyelesaian. Status akan diperiksa otomatis.');

        if ($request->expectsJson()) {
            return response()->json([
                'status' => $payment->status,
                'terminal' => $paid || in_array($payment->status, ['deny', 'cancel', 'expire', 'failure']),
                'message' => $message,
                'redirect_url' => route('bookings.show', $booking),
            ])->header('Cache-Control', 'no-store');
        }

        return back()->with('status', $message);
    }

    public function webhook(Request $r, PaymentService $s)
    {
        try {
            $s->notification($r->all());
        } catch (RequestException|ConnectionException $e) {
            return response()->json(['message' => 'Verifikasi kanal belum tersedia; ulangi notifikasi.'], 503);
        }

        return response()->json(['message' => 'OK']);
    }
}
