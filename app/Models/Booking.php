<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['starts_at' => 'datetime', 'ends_at' => 'datetime', 'hold_expires_at' => 'datetime', 'balance_due_at' => 'datetime'];
    }

    public function court()
    {
        return $this->belongsTo(Court::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function payments()
    {
        return $this->hasMany(PaymentAttempt::class);
    }

    public function refunds()
    {
        return $this->hasMany(RefundRequest::class);
    }

    public function remaining(): int
    {
        return max(0, $this->total - $this->paid_amount);
    }

    public function canCancel(): bool
    {
        return in_array($this->status, ['held', 'confirmed']) && (! $this->paid_amount || now()->lte($this->starts_at->copy()->subHours(config('courtbook.cancel_hours'))));
    }
}
