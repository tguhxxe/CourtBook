<?php

namespace App\Services\Booking;

use App\Models\Booking;
use App\Models\Court;
use App\Models\Maintenance;
use App\Models\RefundRequest;
use App\Models\Reservation;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BookingService
{
    // Acquire SQLite's writer reservation BEFORE any domain read; retries handle contention.
    public function atomic(callable $action): mixed
    {
        return DB::transaction(function () use ($action) {
            Setting::where('id', 1)->increment('lock_version');

            return $action();
        }, 5);
    }

    public function reject(string $message, string $field = 'booking'): never
    {
        throw ValidationException::withMessages([$field => $message]);
    }

    public function expire(): int
    {
        return $this->atomic(fn () => $this->expireLocked());
    }

    public function expireLocked(): int
    {
        $rows = Booking::where(fn ($q) => $q->where(fn ($s) => $s->where('status', 'held')->where('hold_expires_at', '<=', now()))->orWhere(fn ($s) => $s->where('status', 'confirmed')->where('payment_status', 'partial')->where('balance_due_at', '<=', now())))->get();
        foreach ($rows as $b) {
            $b->update(['status' => $b->status === 'held' ? 'expired' : 'cancelled', 'cancellation_reason' => $b->paid_amount ? 'Batas pelunasan terlewati; DP hangus sesuai aturan demo.' : 'Batas pembayaran awal terlewati.']);
            Reservation::where('booking_id', $b->id)->delete();
        }

        return $rows->count();
    }

    public function create(User $user, Court $court, array $input): Booking
    {
        return $this->atomic(function () use ($user, $court, $input) {
            $this->expireLocked();
            $court = $court->fresh();
            $settings = Setting::findOrFail(1);
            $start = Carbon::createFromFormat('Y-m-d H:i', $input['date'].' '.str_pad($input['hour'], 2, '0', STR_PAD_LEFT).':00', config('courtbook.timezone'));
            $duration = (int) $input['duration'];
            $end = $start->copy()->addHours($duration);
            if (! $court->active) {
                $this->reject('Lapangan sedang tidak aktif.');
            }
            if ($duration < 1 || $duration > config('courtbook.max_duration')) {
                $this->reject('Durasi harus 1-4 jam.', 'duration');
            }
            if ($start->lt(now()->addHours(config('courtbook.lead_hours')))) {
                $this->reject('Pilih jadwal minimal 2 jam dari sekarang.', 'hour');
            }
            if ($start->toDateString() > now()->addDays(config('courtbook.advance_days'))->toDateString()) {
                $this->reject('Booking maksimal 30 hari ke depan.', 'date');
            }
            if ($start->hour < $settings->open_hour || $end->toDateString() !== $start->toDateString() || $end->hour > $settings->close_hour) {
                $this->reject('Seluruh durasi harus berada dalam jam operasional.', 'hour');
            }
            if (Reservation::where('court_id', $court->id)->where('starts_at', '>=', $start)->where('starts_at', '<', $end)->exists()) {
                $this->reject('Jadwal terisi atau maintenance. Pilih waktu lain.', 'hour');
            }
            $total = $court->hourly_rate * $duration;
            $b = Booking::create(['code' => 'CB-'.Str::upper(Str::random(10)), 'user_id' => $user->id, 'court_id' => $court->id, 'court_name' => $court->name, 'starts_at' => $start, 'ends_at' => $end, 'duration' => $duration, 'hourly_rate' => $court->hourly_rate, 'dp_percent' => $settings->dp_percent, 'total' => $total, 'dp_amount' => intdiv($total * $settings->dp_percent + 99, 100), 'hold_expires_at' => now()->addMinutes(config('courtbook.hold_minutes')), 'balance_due_at' => $start->copy()->subHours(config('courtbook.lead_hours'))]);
            for ($slot = $start->copy(); $slot->lt($end); $slot->addHour()) {
                Reservation::create(['court_id' => $court->id, 'starts_at' => $slot->copy(), 'booking_id' => $b->id]);
            }

            return $b;
        });
    }

    public function cancel(Booking $booking): void
    {
        $this->atomic(function () use ($booking) {
            $this->expireLocked();
            $b = $booking->fresh();
            if (! $b->canCancel()) {
                $this->reject('Booking tidak dapat dibatalkan. Booking berbayar perlu dibatalkan minimal 24 jam sebelum mulai.');
            }
            $b->update(['status' => 'cancelled', 'cancellation_reason' => 'Dibatalkan pelanggan.']);
            Reservation::where('booking_id', $b->id)->delete();
            if ($b->paid_amount) {
                RefundRequest::firstOrCreate(['reference' => 'cancel-'.$b->id], ['booking_id' => $b->id, 'amount' => $b->paid_amount, 'reason' => 'Pembatalan memenuhi batas 24 jam.']);
            }
        });
    }

    public function maintenance(array $data): Maintenance
    {
        return $this->atomic(function () use ($data) {
            $this->expireLocked();
            $start = Carbon::parse($data['starts_at']);
            $end = Carbon::parse($data['ends_at']);
            if ($start->minute || $start->second || $end->minute || $end->second || $end->lte($start) || $start->lt(now()) || $start->diffInHours($end) > 168) {
                $this->reject('Maintenance harus pada jam bulat, di masa depan, dan maksimal 7 hari.');
            }
            if (Reservation::where('court_id', $data['court_id'])->where('starts_at', '>=', $start)->where('starts_at', '<', $end)->exists()) {
                $this->reject('Maintenance bertumpuk dengan reservasi aktif.');
            }
            $m = Maintenance::create($data);
            for ($s = $start->copy(); $s->lt($end); $s->addHour()) {
                Reservation::create(['court_id' => $m->court_id, 'starts_at' => $s->copy(), 'maintenance_id' => $m->id]);
            }

return $m;
        });
    }
}
