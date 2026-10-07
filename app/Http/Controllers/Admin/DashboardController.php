<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Court;
use App\Models\PaymentAttempt;
use App\Models\RefundRequest;
use App\Services\Booking\BookingService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(BookingService $s)
    {
        $s->expire();

        return view('admin.dashboard', ['bookingCount' => Booking::count(), 'confirmedCount' => Booking::where('status', 'confirmed')->count(), 'courtCount' => Court::where('active', true)->count(), 'refundCount' => RefundRequest::whereIn('status', ['requested', 'reviewing'])->count(), 'bookings' => Booking::with('user')->latest()->take(5)->get()]);
    }

    public function bookings(Request $r)
    {
        $r->validate(['q' => 'nullable|string|max:100']);
        $bookings = Booking::with('user')->when($r->q, fn ($q, $text) => $q->where('code', 'like', '%'.$text.'%'))->latest()->paginate(15)->withQueryString();

        return view('admin.bookings', compact('bookings'));
    }

    public function transactions()
    {
        $payments = PaymentAttempt::with('booking')->latest()->paginate(15);

        return view('admin.transactions', compact('payments'));
    }
}
