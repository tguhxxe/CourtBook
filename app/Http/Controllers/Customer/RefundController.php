<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\CustomerRefundRequest;
use App\Models\Booking;
use App\Models\RefundRequest;
use App\Services\Booking\BookingService;
use Illuminate\Http\Request;

class RefundController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->role === 'customer', 403);
        $refunds = RefundRequest::with('booking')
            ->whereHas('booking', fn ($query) => $query->where('user_id', $request->user()->id))
            ->latest()->paginate(10);

        return view('customer.refunds', compact('refunds'));
    }

    public function store(CustomerRefundRequest $request, Booking $booking, BookingService $service)
    {
        $service->cancel($booking, $request->validated('reason'));

        return redirect()->route('refunds.index')->with('status', 'Refund diajukan dan booking dibatalkan. Admin akan meninjau permintaan Anda.');
    }
}
