@auth
    <a href="{{ route('bookings.index') }}" @if(request()->routeIs('bookings.index')) aria-current="page" @endif @class(['current' => request()->routeIs('bookings.*')])>Booking saya</a>
    @if(auth()->user()->role === 'customer')
        <a href="{{ route('refunds.index') }}" @class(['current' => request()->routeIs('refunds.*')])>Refund saya</a>
    @endif
    <a href="{{ route('profile.edit') }}">Profil</a>
    @if(auth()->user()->role === 'admin')
        <a href="{{ route('admin.dashboard') }}">Admin</a>
    @endif
    <form novalidate method="post" action="{{ route('logout') }}">
        @csrf
        <button class="btn ghost" type="submit">Keluar</button>
    </form>
@else
    <a href="{{ route('login') }}">Masuk</a>
    <a class="btn primary small" href="{{ route('register') }}">Buat akun <span aria-hidden="true">&nearr;</span></a>
@endauth
