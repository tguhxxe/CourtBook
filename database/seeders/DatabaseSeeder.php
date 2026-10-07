<?php

namespace Database\Seeders;

use App\Models\Court;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Setting::firstOrCreate(['id' => 1], ['open_hour' => 7, 'close_hour' => 23, 'dp_percent' => 50]);
        foreach ([['Tennis Court A', 'tennis', 100000], ['Tennis Court B', 'tennis', 100000], ['Mini Soccer Arena', 'soccer', 300000]] as [$name,$sport,$rate]) {
            Court::firstOrCreate(['name' => $name], ['sport' => $sport, 'hourly_rate' => $rate, 'description' => $sport === 'tennis' ? 'Lapangan tenis hard court untuk latihan dan pertandingan santai. Seluruh fasilitas merupakan data simulasi.' : 'Arena mini soccer untuk bermain bersama tim. Seluruh fasilitas merupakan data simulasi.', 'facilities' => ['Lampu lapangan', 'Area duduk', 'Parkir', 'Toilet'], 'active' => true]);
        }
        if (app()->environment(['local', 'testing'])) {
            foreach ([['Teguh Setia', 'pelanggan@courtbook.test', 'customer'], ['Admin Demo', 'admin@courtbook.test', 'admin']] as [$name,$email,$role]) {
                if (! User::where('email', $email)->exists()) {
                    $u = new User(['name' => $name, 'email' => $email, 'password' => Hash::make('CourtBook123!')]);
                    $u->role = $role;
                    $u->save();
                }
            }
        }
    }
}
