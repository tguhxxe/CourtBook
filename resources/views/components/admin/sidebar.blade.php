<aside class="admin-sidebar">
    <a class="brand" href="{{ route('admin.dashboard') }}"><x-logo/>CourtBook.</a>
    <p class="admin-sidebar-label">Operasional lapangan</p>
    <nav aria-label="Navigasi admin">
        @foreach(['dashboard' => 'Ringkasan', 'courts.index' => 'Lapangan', 'maintenance' => 'Maintenance', 'bookings' => 'Booking', 'transactions' => 'Transaksi', 'refunds' => 'Permintaan refund', 'settings' => 'Pengaturan'] as $route => $label)
            @php($current = request()->routeIs('admin.'.$route) || ($route === 'courts.index' && request()->routeIs('admin.courts.*')) || ($route === 'bookings' && request()->routeIs('bookings.show')))
            <a href="{{ route('admin.'.$route) }}" @class(['current' => $current]) @if($current) aria-current="page" @endif>{{ $label }}</a>
        @endforeach
    </nav>
    <div class="admin-sidebar-actions">
        <a class="btn outline" href="{{ route('home') }}">Lihat website &nearr;</a>
        <form novalidate action="{{ route('logout') }}" method="post">@csrf<button class="btn ghost">Keluar</button></form>
    </div>
    <p class="sidebar-note">CourtBook Sport Center<br>Lingkungan simulasi praktikum</p>
</aside>
