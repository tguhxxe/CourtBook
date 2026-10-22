@extends('layouts.home', ['inlineFeedback' => true])
@section('title', 'Masuk')
@section('description', 'Masuk ke CourtBook untuk melanjutkan booking dan mengelola jadwal bermain tenis atau mini soccer.')
@section('content')
<div class="home-wrap sports-login">
    <aside class="sports-login-visual" aria-label="Ilustrasi olahraga">
        <img src="{{ asset('images/home/blue-tennis-court.webp') }}" width="1536" height="1024" alt="" fetchpriority="high">
        <div class="sports-login-story">
            <p>Kembali ke<br>permainanmu.</p>
            <span>Temukan waktu untuk bermain bersama tim.</span>
        </div>
        <span class="sports-login-caption">Ilustrasi lapangan, bukan foto lokasi sebenarnya.</span>
    </aside>
    <section class="sports-login-form" aria-labelledby="login-title">
        <h1 id="login-title">Masuk untuk main.</h1>
        <p class="sports-login-description">Lanjutkan booking dan kelola jadwal bermainmu dari satu akun.</p>
        @include('components.feedback')
        <form novalidate method="post" action="{{ route('login.store') }}">
            @csrf
            <x-field name="email" label="Email" type="email" autocomplete="username" placeholder="nama@email.com" required/>
            <x-field name="password" label="Kata sandi" type="password" autocomplete="current-password" required/>
            <div class="sports-login-options">
                <label class="check"><input type="checkbox" name="remember" value="1" @checked(old('remember'))> Ingat saya</label>
                <a href="{{ route('password.request') }}">Lupa kata sandi?</a>
            </div>
            <button type="submit" class="home-button home-button-blue sports-login-submit"><span>Masuk</span><span class="sports-login-busy" aria-hidden="true">Memproses…</span></button>
        </form>
        <p class="sports-login-register">Belum punya akun? <a href="{{ route('register') }}">Buat akun</a></p>
        <p class="sports-login-demo">Website simulasi untuk proyek praktikum.</p>
    </section>
</div>
@endsection
