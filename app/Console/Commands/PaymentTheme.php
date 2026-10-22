<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PaymentTheme extends Command
{
    protected $signature = 'courtbook:payment-theme {--apply : Simpan tema ke Midtrans Sandbox}';

    protected $description = 'Pratinjau atau terapkan tema CourtBook pada Snap Sandbox tanpa membuat transaksi';

    private const ENDPOINT = 'https://app.sandbox.midtrans.com/snap/v3/merchant-preferences';

    public function handle(): int
    {
        $key = config('courtbook.midtrans.server_key');
        if (! $key) {
            $this->error('Server key Sandbox belum dikonfigurasi.');

            return self::FAILURE;
        }

        try {
            $client = Http::withBasicAuth($key, '')->acceptJson()->asJson()
                ->withOptions(['allow_redirects' => false])->timeout(15)->connectTimeout(5);
            $before = $client->get(self::ENDPOINT)->throw()->json();
            if (! is_array($before) || ! isset($before['brand']) || ! is_array($before['brand'])) {
                $this->error('Preferensi Snap tidak valid; tidak ada perubahan.');

                return self::FAILURE;
            }
            $desired = [
                'display_name' => 'CourtBook',
                'locale' => 'id',
                'brand' => array_replace($before['brand'], [
                    'typography' => 'Nunito',
                    'header_color' => '#102c59',
                    'button_color' => '#1455d9',
                    'input_border_radius' => 6,
                    'button_border_radius' => 6,
                    'hide_header' => false,
                ]),
            ];
            $this->info('CourtBook | Bahasa Indonesia | Nunito | header #102c59 | tombol #1455d9 | radius 6px');
            if (! $this->option('apply')) {
                $this->line('Pratinjau saja. Gunakan --apply untuk menyimpan tampilan pada merchant Sandbox ini.');

                return self::SUCCESS;
            }
            if (Arr::only($before, array_keys($desired)) == $desired) {
                $this->info('Tema sudah sesuai; tidak ada perubahan.');

                return self::SUCCESS;
            }
            $backup = 'midtrans-theme/'.now()->format('Ymd-His').'-'.Str::uuid().'.json';
            if (! Storage::disk('local')->put($backup, json_encode([
                'merchant_id' => $before['merchant_id'] ?? null,
                'before' => Arr::only($before, array_keys($desired)),
                'after' => $desired,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR))) {
                $this->error('Backup tidak dapat disimpan; tidak ada perubahan.');

                return self::FAILURE;
            }
            $this->line('Backup tampilan: '.Storage::disk('local')->path($backup));
            $client->patch(self::ENDPOINT, $desired)->throw();
            $after = $client->get(self::ENDPOINT)->throw()->json();
            if (! is_array($after) || Arr::only($after, array_keys($desired)) != $desired) {
                $this->error('Respons tersimpan belum cocok dengan tema. Periksa preferensi Snap; backup tersedia.');

                return self::FAILURE;
            }
            if (Arr::except($before, array_keys($desired)) != Arr::except($after, array_keys($desired))) {
                $this->error('Preferensi selain tampilan berbeda pada pemeriksaan ulang. Periksa dashboard; backup tersedia.');

                return self::FAILURE;
            }
            $this->info('Tema Snap Sandbox tersimpan dan terverifikasi. Preferensi selain tampilan tetap sama.');

            return self::SUCCESS;
        } catch (\Throwable) {
            // Never print provider request details or credentials.
            $this->error('Preferensi Midtrans belum dapat diproses. Periksa koneksi dan akses Server Key Sandbox.');

            return self::FAILURE;
        }
    }
}
