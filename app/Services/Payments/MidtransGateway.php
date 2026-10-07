<?php

namespace App\Services\Payments;

use App\Models\PaymentAttempt;
use Illuminate\Support\Facades\Http;

class MidtransGateway
{
    public function configured(): bool
    {
        return (bool) (config('courtbook.midtrans.server_key') && config('courtbook.midtrans.client_key'));
    }

    private function client()
    {
        return Http::withBasicAuth(config('courtbook.midtrans.server_key'), '')->acceptJson()->timeout(15)->connectTimeout(5);
    }

    public function snap(PaymentAttempt $attempt): string
    {
        $b = $attempt->booking;
        $payload = ['transaction_details' => ['order_id' => $attempt->order_id, 'gross_amount' => $attempt->amount], 'customer_details' => ['first_name' => $b->user->name, 'email' => $b->user->email], 'expiry' => ['start_time' => now()->format('Y-m-d H:i:s O'), 'unit' => 'minutes', 'duration' => max(1, (int) ceil(now()->diffInSeconds($attempt->expires_at, false) / 60))]];
        $public = config('courtbook.midtrans.public_url');
        if ($public) {
            $payload['callbacks'] = ['finish' => rtrim($public, '/').'/bookings/'.$b->id];
        }
        $token = $this->client()->post(config('courtbook.midtrans.snap_url'), $payload)->throw()->json('token');
        if (! is_string($token) || ! $token) {
            throw new \RuntimeException('Invalid Snap response');
        }

return $token;
    }

    public function status(string $order): array
    {
        return $this->client()->get(config('courtbook.midtrans.api_url').'/'.rawurlencode($order).'/status')->throw()->json();
    }
}
