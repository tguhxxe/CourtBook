<?php

return [
    'timezone' => 'Asia/Jakarta', 'hold_minutes' => 15, 'advance_days' => 30,
    'lead_hours' => 2, 'cancel_hours' => 24, 'max_duration' => 4,
    'midtrans' => ['server_key' => env('MIDTRANS_SERVER_KEY'), 'client_key' => env('MIDTRANS_CLIENT_KEY'),
        'merchant_id' => env('MIDTRANS_MERCHANT_ID'), 'public_url' => env('MIDTRANS_PUBLIC_URL'),
        'snap_url' => 'https://app.sandbox.midtrans.com/snap/v1/transactions',
        'api_url' => 'https://api.sandbox.midtrans.com/v2',
        'script_url' => 'https://app.sandbox.midtrans.com/snap/snap.js'],
];
