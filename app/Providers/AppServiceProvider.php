<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        ResetPassword::toMailUsing(function (object $notifiable, string $token) {
            $url = route('password.reset', ['token' => $token, 'email' => $notifiable->getEmailForPasswordReset()]);

            return (new MailMessage)->subject('Reset kata sandi CourtBook')->greeting('Halo!')->line('Kami menerima permintaan reset kata sandi akun CourtBook Anda.')->action('Reset kata sandi', $url)->line('Tautan berlaku 60 menit. Jika tidak meminta reset, abaikan pesan ini.')->salutation('CourtBook - demo lokal');
        });
    }
}
