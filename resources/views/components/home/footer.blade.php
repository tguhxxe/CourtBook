<footer class="home-footer">
    <div class="home-wrap home-footer-top">
        <div>
            <a class="home-brand" href="{{ route('home') }}"><x-logo/><span>CourtBook.</span></a>
            <p>Waktu bermain dimulai dari sini.</p>
        </div>
        <nav aria-label="Navigasi footer">
            <a href="{{ route('courts.index') }}">Cari lapangan</a>
            <a href="{{ request()->routeIs('home') ? '#cara-booking' : route('home').'#cara-booking' }}">Cara booking</a>
            @guest<a href="{{ route('login') }}">Masuk</a>@else<a href="{{ route('bookings.index') }}">Booking saya</a>@endguest
        </nav>
    </div>
    <x-home.payment-methods/>
    <div class="home-wrap home-footer-bottom"><span>&copy; {{ now()->year }} CourtBook</span><span>Proyek praktikum PKPL/SQA</span></div>
</footer>
