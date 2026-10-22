<!doctype html>
<html lang="id" class="home-theme">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Temukan lapangan tenis dan mini soccer, lihat jadwal, lalu pesan waktu bermainmu di CourtBook. Website simulasi praktikum.">
    <title>@yield('title', 'Waktunya main') | CourtBook</title>
    <link rel="preload" href="{{ Vite::asset('resources/fonts/Manrope-Variable.ttf') }}" as="font" type="font/ttf" crossorigin>
    <link rel="preload" href="{{ Vite::asset('resources/fonts/BarlowCondensed-Bold.ttf') }}" as="font" type="font/ttf" crossorigin>
    @vite(['resources/css/app.css', 'resources/css/home.css', 'resources/js/app.js'])
</head>
<body class="home-page">
    <a class="skip" href="#main">Lewati ke konten</a>
    <header class="home-header">
        <div class="home-wrap home-navbar">
            <a class="home-brand" href="{{ route('home') }}" aria-label="CourtBook, beranda"><x-logo/><span>CourtBook<span class="home-brand-dot">.</span></span></a>
            <nav class="home-desktop-nav" aria-label="Navigasi utama">
                <a href="{{ route('home') }}" aria-current="page">Beranda</a>
                <a href="{{ route('courts.index') }}">Cari lapangan</a>
                <a href="#cara-booking">Cara booking</a>
            </nav>
            <div class="home-desktop-account">
                @auth
                    <details class="home-account-menu">
                        <summary>Akun saya <x-home-icon name="chevron"/></summary>
                        <nav aria-label="Akun">@include('components.customer-account-links')</nav>
                    </details>
                @else
                    <nav aria-label="Akun">@include('components.customer-account-links')</nav>
                @endauth
            </div>
        </div>
        <details class="home-mobile-menu">
                <summary>Menu <x-home-icon name="menu"/></summary>
                <nav class="home-wrap" aria-label="Navigasi ponsel">
                    <a href="{{ route('home') }}" aria-current="page">Beranda</a>
                    <a href="{{ route('courts.index') }}">Cari lapangan</a>
                    <a href="#cara-booking">Cara booking</a>
                    @include('components.customer-account-links')
                </nav>
        </details>
    </header>
    <main id="main" tabindex="-1">
        <div class="home-wrap home-feedback">@include('components.feedback')</div>
        @yield('content')
    </main>
    <footer class="home-footer">
        <div class="home-wrap home-footer-top">
            <div>
                <a class="home-brand" href="{{ route('home') }}"><x-logo/><span>CourtBook.</span></a>
                <p>Waktu bermain dimulai dari sini.</p>
            </div>
            <nav aria-label="Navigasi footer">
                <a href="{{ route('courts.index') }}">Cari lapangan</a>
                <a href="#cara-booking">Cara booking</a>
                @guest<a href="{{ route('login') }}">Masuk</a>@else<a href="{{ route('bookings.index') }}">Booking saya</a>@endguest
            </nav>
        </div>
        <section class="home-wrap home-payments" aria-labelledby="home-payments-title">
            <h2 id="home-payments-title">Metode pembayaran</h2>
            <p>Metode yang tersedia ditampilkan saat pembayaran.</p>
            <ul class="home-payment-logos" aria-label="Bank dan layanan pembayaran">
                @foreach(['bca' => 'BCA', 'bri' => 'BRI (BRIVA)', 'mandiri' => 'Mandiri', 'bni' => 'BNI', 'bsi' => 'BSI', 'gopay' => 'GoPay', 'dana' => 'DANA', 'qris' => 'QRIS'] as $logo => $name)
                    <li><img src="{{ asset('images/payments/'.$logo.'.png') }}" alt="{{ $name }}" width="100" height="32" loading="lazy"></li>
                @endforeach
            </ul>
        </section>
        <div class="home-wrap home-footer-bottom"><span>&copy; {{ now()->year }} CourtBook</span><span>Proyek praktikum PKPL/SQA</span></div>
    </footer>
    @include('components.confirm-dialog')
</body>
</html>
