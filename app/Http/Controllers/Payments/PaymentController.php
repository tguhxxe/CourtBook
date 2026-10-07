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

    public function reconcile(PaymentAttempt $payment, PaymentService $s)
    {
        Gate::authorize('view', $payment->booking);
        try {
            $s->reconcile($payment);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()->withErrors(['payment' => 'Status belum dapat diperiksa. Coba kembali; jangan membuat pembayaran kedua.']);
        }

return back()->with('status', 'Status transaksi diperiksa dari server pembayaran.');
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
