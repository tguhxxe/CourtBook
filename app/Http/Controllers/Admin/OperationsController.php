<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MaintenanceRequest;
use App\Http\Requests\RefundRequest as RefundForm;
use App\Http\Requests\SettingsRequest;
use App\Models\Booking;
use App\Models\Court;
use App\Models\Maintenance;
use App\Models\RefundRequest;
use App\Models\Reservation;
use App\Models\Setting;
use App\Services\Booking\BookingService;

class OperationsController extends Controller
{
    public function maintenance()
    {
        return view('admin.maintenance', ['maintenances' => Maintenance::with('court')->latest()->paginate(15), 'courts' => Court::orderBy('name')->get()]);
    }

    public function storeMaintenance(MaintenanceRequest $r, BookingService $s)
    {
        $s->maintenance($r->validated());

        return back()->with('status', 'Jadwal maintenance berhasil diblok.');
    }

    public function destroyMaintenance(Maintenance $maintenance, BookingService $s)
    {
        $s->atomic(function () use ($maintenance) {
            Reservation::where('maintenance_id', $maintenance->id)->delete();
            $maintenance->delete();
        });

        return back()->with('status', 'Blok maintenance dilepas.');
    }

    public function settings()
    {
        return view('admin.settings', ['settings' => Setting::findOrFail(1)]);
    }

    public function updateSettings(SettingsRequest $r, BookingService $s)
    {
        $s->atomic(function () use ($r, $s) {
            $s->expireLocked();
            $d = $r->validated();
            if (Booking::whereIn('status', ['held', 'confirmed'])->where('ends_at', '>', now())->get()->contains(fn ($b) => $b->starts_at->hour < $d['open_hour'] || $b->ends_at->hour > $d['close_hour'])) {
                $s->reject('Jam baru bertentangan dengan booking aktif. Selesaikan booking tersebut lebih dahulu.');
            } Setting::findOrFail(1)->update($d);
        });

        return back()->with('status', 'Pengaturan disimpan untuk booking baru.');
    }

    public function refunds()
    {
        return view('admin.refunds', ['refunds' => RefundRequest::with('booking')->latest()->paginate(15)]);
    }

    public function updateRefund(RefundForm $r, RefundRequest $refund, BookingService $s)
    {
        $s->atomic(function () use ($r, $refund, $s) {
            $current = $refund->fresh();
            $allowed = ['requested' => ['requested', 'reviewing', 'rejected'], 'reviewing' => ['reviewing', 'processed', 'rejected'], 'processed' => ['processed'], 'rejected' => ['rejected', 'reviewing']];
            if (! in_array($r->status, $allowed[$current->status])) {
                $s->reject('Perubahan progres refund tidak sesuai. Tinjau permintaan dahulu.');
            } $current->update($r->validated() + ['handled_by' => $r->user()->id]);
        });

        return back()->with('status','Progres refund dicatat. Catatan ini tidak menjalankan transfer dana.');
    }
}
