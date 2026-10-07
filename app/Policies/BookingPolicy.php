<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;

class BookingPolicy
{
    public function view(User $u, Booking $b): bool
    {
        return $u->role === 'admin' || $u->id === $b->user_id;
    }

    public function pay(User $u, Booking $b): bool
    {
        return $u->role === 'customer' && $u->id === $b->user_id;
    }

    public function cancel(User $u, Booking $b): bool
    {
        return $this->pay($u, $b);
    }
}
