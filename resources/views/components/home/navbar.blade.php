<header class="home-header">
    <div class="home-wrap home-navbar">
        <a class="home-brand" href="{{ route('home') }}" aria-label="CourtBook, beranda"><x-logo/><span>CourtBook<span class="home-brand-dot">.</span></span></a>
        <nav class="home-desktop-nav" aria-label="Navigasi utama">
            <a href="{{ route('home') }}" @if(request()->routeIs('home')) aria-current="page" @endif>Beranda</a>
            <a href="{{ route('courts.index') }}" @if(request()->routeIs('courts.index')) aria-current="page" @endif>Cari lapangan</a>
            <a href="{{ request()->routeIs('home') ? '#cara-booking' : route('home').'#cara-booking' }}">Cara booking</a>
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
            <a href="{{ route('home') }}" @if(request()->routeIs('home')) aria-current="page" @endif>Beranda</a>
            <a href="{{ route('courts.index') }}" @if(request()->routeIs('courts.index')) aria-current="page" @endif>Cari lapangan</a>
            <a href="{{ request()->routeIs('home') ? '#cara-booking' : route('home').'#cara-booking' }}">Cara booking</a>
            @include('components.customer-account-links')
        </nav>
    </details>
</header>
