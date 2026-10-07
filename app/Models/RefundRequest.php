<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RefundRequest extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [];
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
