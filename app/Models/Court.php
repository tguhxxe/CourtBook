<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Court extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['facilities' => 'array', 'active' => 'boolean', 'hourly_rate' => 'integer'];
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
