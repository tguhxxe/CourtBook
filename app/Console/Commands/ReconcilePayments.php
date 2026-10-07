<?php

namespace App\Console\Commands;

use App\Models\PaymentAttempt;
use App\Services\Payments\PaymentService;
use Illuminate\Console\Command;

class ReconcilePayments extends Command
{
    protected $signature = 'courtbook:reconcile';

    protected $description = 'Periksa status transaksi langsung ke Midtrans Sandbox';

    public function handle(PaymentService $service): int
    {
        $failed = 0;
        PaymentAttempt::whereIn('status', ['creating', 'uncertain', 'pending'])->chunkById(50, function ($rows) use ($service, &$failed) {
            foreach ($rows as $a) {
                try {
                    $service->reconcile($a);
                } catch (\Throwable $e) {
                    $failed++;
                    $this->warn('Belum dapat memverifikasi '.$a->order_id.'; akan diperiksa ulang.');
                }
            }
        });
        $this->info('Selesai; '.$failed.' transaksi perlu diperiksa ulang.');

        return $failed ? self::FAILURE : self::SUCCESS;
    }
}
