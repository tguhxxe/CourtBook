<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class BootstrapAdmin extends Command
{
    protected $signature = 'courtbook:bootstrap-admin';

    protected $description = 'Buat admin awal dari konfigurasi hosting tanpa mengubah akun yang sudah ada';

    public function handle(): int
    {
        if (User::where('role', 'admin')->exists()) {
            $this->info('Admin sudah tersedia; akun dan password tidak diubah.');

            return self::SUCCESS;
        }
        $email = config('hosting.admin_email');
        $password = config('hosting.admin_password');
        if (! is_string($email) || ! filter_var($email, FILTER_VALIDATE_EMAIL) || ! is_string($password) || strlen($password) < 12) {
            $this->error('Isi INITIAL_ADMIN_EMAIL yang valid dan INITIAL_ADMIN_PASSWORD minimal 12 karakter.');

            return self::FAILURE;
        }
        if (User::where('email', $email)->exists()) {
            $this->error('Email admin sudah digunakan akun pelanggan. Pilih email lain.');

            return self::FAILURE;
        }
        $user = new User(['name' => 'Admin CourtBook', 'email' => $email, 'password' => Hash::make($password)]);
        $user->role = 'admin';
        $user->save();
        $this->info('Admin awal berhasil dibuat.');

        return self::SUCCESS;
    }
}
