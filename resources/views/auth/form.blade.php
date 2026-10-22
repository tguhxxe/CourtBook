@extends('layouts.customer')
@php($titles = ['login' => 'Masuk untuk bermain.', 'register' => 'Mulai permainanmu.', 'forgot' => 'Lupa kata sandi?', 'reset' => 'Buat kata sandi baru.'])
@section('title', $titles[$mode])
@section('content')
<div class="account-auth">
    <aside class="account-auth-story" aria-label="Ilustrasi olahraga">
        <img src="{{ asset('images/home/blue-tennis-court.webp') }}" width="1536" height="1024" alt="">
        <div><p>Satu akun.<br>Lebih banyak main.</p><span>Atur booking tenis dan mini soccer dari satu tempat.</span></div>
        <small>Ilustrasi lapangan. Website simulasi praktikum.</small>
    </aside>
    <section class="account-auth-form" aria-labelledby="account-auth-title">
        <h1 id="account-auth-title">{{ $titles[$mode] }}</h1>
        <p class="muted">{{ $mode === 'forgot' ? 'Masukkan email terdaftar. Pada demo lokal, tautan reset dicatat di mail log dan tidak dikirim sebagai email sungguhan.' : ($mode === 'register' ? 'Buat akun untuk menyimpan pesanan dan mengatur jadwal bermainmu.' : 'Gunakan kata sandi baru untuk melanjutkan ke akunmu.') }}</p>
        <form novalidate method="post" action="{{ route(['login' => 'login.store', 'register' => 'register.store', 'forgot' => 'password.email', 'reset' => 'password.update'][$mode]) }}">
            @csrf
            @if($mode === 'reset')<input type="hidden" name="token" value="{{ $token }}">@endif
            @if($mode === 'register')<x-field name="name" label="Nama lengkap" autocomplete="name" required maxlength="100"/>@endif
            <x-field name="email" label="Email" type="email" :value="$email ?? null" autocomplete="email" required/>
            @if($mode !== 'forgot')<x-field name="password" label="Kata sandi" type="password" :autocomplete="$mode === 'login' ? 'current-password' : 'new-password'" hint="Minimal 8 karakter untuk kata sandi baru." required/>@endif
            @if(in_array($mode, ['register', 'reset']))<x-field name="password_confirmation" label="Konfirmasi kata sandi" type="password" autocomplete="new-password" required/>@endif
            @if($mode === 'login')<div class="form-meta"><label class="check"><input type="checkbox" name="remember" value="1"> Ingat saya</label><a class="text-link" href="{{ route('password.request') }}">Lupa kata sandi?</a></div>@endif
            <button type="submit" class="btn primary wide">{{ ['login' => 'Masuk', 'register' => 'Buat akun pelanggan', 'forgot' => 'Minta tautan reset', 'reset' => 'Simpan kata sandi'][$mode] }}</button>
        </form>
        <p class="auth-bottom"><a class="text-link" href="{{ route('login') }}">Kembali ke halaman masuk</a></p>
    </section>
</div>
@endsection
