<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Court;
use App\Models\Reservation;
use App\Models\Setting;
use App\Services\Booking\BookingService;
use Illuminate\Http\Request;

class CourtController extends Controller
{
    public function index(Request $r)
    {
        $data = $r->validate(['q' => 'nullable|string|max:100', 'sport' => 'nullable|in:tennis,soccer']);
        $courts = Court::where('active', true)->when($data['q'] ?? null, fn ($q, $text) => $q->where('name', 'like', '%'.$text.'%'))->when($data['sport'] ?? null, fn ($q, $sport) => $q->where('sport', $sport))->orderBy('name')->paginate(9)->withQueryString();

        return view('customer.courts.index', compact('courts'));
    }

    public function show(Request $r, Court $court, BookingService $service)
    {
        abort_unless($court->active, 404);
        $r->validate(['date' => 'nullable|date_format:Y-m-d|after_or_equal:today|before_or_equal:'.now()->addDays(30)->toDateString()]);
        $service->expire();
        $date = $r->input('date', now()->toDateString());
        $slots = Reservation::where('court_id', $court->id)->whereDate('starts_at', $date)->get()->keyBy(fn ($s) => substr($s->starts_at, 11, 2));
        $settings = Setting::findOrFail(1);

        return view('customer.courts.show', compact('court','date','slots','settings'));
    }
}
