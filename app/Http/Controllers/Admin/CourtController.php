<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CourtRequest;
use App\Models\Court;
use App\Models\Maintenance;
use App\Services\Booking\BookingService;

class CourtController extends Controller
{
    public function index()
    {
        return view('admin.courts.index', ['courts' => Court::orderBy('name')->paginate(15)]);
    }

    public function create()
    {
        return view('admin.courts.form', ['court' => new Court(['active' => true, 'facilities' => [], 'hourly_rate' => 100000, 'sport' => 'tennis'])]);
    }

    public function edit(Court $court)
    {
        return view('admin.courts.form', compact('court'));
    }

    private function data(CourtRequest $r): array
    {
        $d = $r->validated();
        $d['facilities'] = array_values(array_filter(array_map('trim', explode(',', $d['facilities']))));

        return $d;
    }

    public function store(CourtRequest $r, BookingService $s)
    {
        $s->atomic(fn () => Court::create($this->data($r)));

        return redirect()->route('admin.courts.index')->with('status', 'Lapangan berhasil ditambahkan.');
    }

    public function update(CourtRequest $r, Court $court, BookingService $s)
    {
        $s->atomic(fn () => $court->update($this->data($r)));

        return redirect()->route('admin.courts.index')->with('status', 'Lapangan berhasil diperbarui. Tarif booking lama tetap.');
    }

    public function destroy(Court $court, BookingService $s)
    {
        $s->atomic(function () use ($court, $s) {
            if ($court->bookings()->exists() || Maintenance::where('court_id', $court->id)->exists()) {
                $s->reject('Lapangan memiliki riwayat. Nonaktifkan lapangan untuk menjaga data.');
            } $court->delete();
        });

        return back()->with('status','Lapangan berhasil dihapus.');
    }
}
