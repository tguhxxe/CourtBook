<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\BookingRequest;
use App\Models\Booking;
use App\Models\Court;
use App\Services\Booking\BookingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class BookingController extends Controller
{
    public function index(Request $r, BookingService $s)
    {
        $s->expire();
        $r->validate(['status' => 'nullable|in:held,confirmed,cancelled,expired']);
        $bookings = $r->user()->bookings()->when($r->status, fn ($q, $status) => $q->where('status', $status))->latest()->paginate(10)->withQueryString();

        return view('customer.bookings.index', compact('bookings'));
    }

    public function store(BookingRequest $r, BookingService $s)
    {
        $b = $s->create($r->user(), Court::findOrFail($r->court_id), $r->validated());

        return redirect()->route('bookings.show', $b)->with('status', 'Jadwal ditahan 15 menit. Selesaikan pembayaran awal sebelum batas waktu.');
    }

    public function show(Booking $booking, BookingService $s)
    {
        Gate::authorize('view', $booking);
        $s->expire();
        $booking->refresh()->load(['payments', 'refunds']);

        return view('customer.bookings.show', compact('booking'));
    }

    public function cancel(Booking $booking, BookingService $s)
    {
        Gate::authorize('cancel', $booking);
        $s->cancel($booking);

        return back()->with('status','Booking dibatalkan. Permintaan refund dicatat bila memenuhi syarat; diproses manual oleh admin.');
    }
}
