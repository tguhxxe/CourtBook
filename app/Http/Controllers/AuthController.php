<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuthRequest;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login()
    {
        return view('auth.form', ['mode' => 'login']);
    }

    public function register()
    {
        return view('auth.form', ['mode' => 'register']);
    }

    public function forgot()
    {
        return view('auth.form', ['mode' => 'forgot']);
    }

    public function reset(Request $r, string $token)
    {
        return view('auth.form', ['mode' => 'reset', 'token' => $token, 'email' => $r->email]);
    }

    public function store(AuthRequest $r)
    {
        $user = User::create($r->safe()->only(['name', 'email', 'password']));
        Auth::login($user);
        $r->session()->regenerate();

        return redirect()->route('bookings.index')->with('status', 'Akun berhasil dibuat. Pilih lapangan untuk booking pertama Anda.');
    }

    public function authenticate(AuthRequest $r)
    {
        if (! Auth::attempt($r->safe()->only(['email', 'password']), $r->boolean('remember'))) {
            throw ValidationException::withMessages(['email' => 'Email atau kata sandi tidak sesuai.']);
        } $r->session()->regenerate();

        return redirect()->intended($r->user()->role === 'admin' ? route('admin.dashboard') : route('bookings.index'));
    }

    public function logout(Request $r)
    {
        Auth::logout();
        $r->session()->invalidate();
        $r->session()->regenerateToken();

        return redirect()->route('home');
    }

    public function sendReset(AuthRequest $r)
    {
        Password::sendResetLink($r->safe()->only('email'));

        return back()->with('status', config('mail.default') === 'log' ? 'Jika akun terdaftar, tautan reset dicatat pada mail log lokal. Tidak ada email sungguhan yang dikirim.' : 'Jika akun terdaftar, tautan reset telah diminta.');
    }

    public function updatePassword(AuthRequest $r)
    {
        $result = Password::reset($r->safe()->only(['email', 'password', 'password_confirmation', 'token']), function (User $u, string $password) {
            $u->forceFill(['password' => Hash::make($password), 'remember_token' => Str::random(60)])->save();
            event(new PasswordReset($u));
        });
        if ($result !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages(['email' => __($result)]);
        }

return redirect()->route('login')->with('status', 'Kata sandi berhasil diperbarui. Silakan masuk.');
    }
}
