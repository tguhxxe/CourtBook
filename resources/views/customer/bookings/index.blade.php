@extends('layouts.home')
@section('title', 'Booking saya')
@section('description', 'Lihat jadwal bermain, status pembayaran, dan detail booking lapanganmu di CourtBook.')
@section('content')
<div class="my-bookings">
    <section class="my-bookings-intro" aria-labelledby="my-bookings-title">
        <div class="home-wrap my-bookings-heading">
            <div><h1 id="my-bookings-title">Booking saya.</h1><p>Jadwal bermain dan status pesananmu, dalam satu tempat.</p></div>
            <a class="home-button home-button-blue" href="{{ route('courts.index') }}">Cari lapangan <x-home-icon name="arrow"/></a>
        </div>
    </section>
    <section class="home-wrap my-bookings-content" aria-label="Daftar booking saya">
        <div class="my-bookings-toolbar">
            <form novalidate method="get" action="{{ route('bookings.index') }}" class="my-bookings-filter">
                <div class="field">
                    <label for="status">Status booking</label>
                    <select id="status" name="status" aria-invalid="{{ $errors->has('status') ? 'true' : 'false' }}" aria-describedby="status-help">
                        <option value="">Semua status</option>
                        @foreach(['held' => 'Menunggu pembayaran', 'confirmed' => 'Dikonfirmasi', 'cancelled' => 'Dibatalkan', 'expired' => 'Kedaluwarsa'] as $value => $label)
                            <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <small id="status-help" class="field-error">{{ $errors->first('status') }}</small>
                </div>
                <button type="submit" class="home-button my-bookings-filter-button">Tampilkan</button>
                @if(request()->filled('status'))<a class="my-bookings-reset" href="{{ route('bookings.index') }}">Semua booking</a>@endif
            </form>
            <p class="my-bookings-count">{{ $bookings->total() }} booking{{ request()->filled('status') ? ' ditemukan' : '' }}</p>
        </div>
        @if($bookings->count())
            <ul class="my-bookings-list" role="list">
                @foreach($bookings as $booking)
                    <li><x-customer.booking-summary :booking="$booking"/></li>
                @endforeach
            </ul>
        @else
            <div class="my-bookings-empty">
                <h2>{{ request()->filled('status') ? 'Tidak ada booking dengan status ini.' : 'Belum ada jadwal bermain.' }}</h2>
                <p>{{ request()->filled('status') ? 'Coba status lain atau tampilkan semua bookingmu.' : 'Pilih lapangan dan waktu bermain. Pesananmu akan muncul di sini setelah booking dibuat.' }}</p>
                <a class="home-button home-button-blue" href="{{ request()->filled('status') ? route('bookings.index') : route('courts.index') }}">{{ request()->filled('status') ? 'Lihat semua booking' : 'Cari lapangan' }}</a>
            </div>
        @endif
        {{ $bookings->links('components.home.pagination', ['itemLabel' => 'booking', 'paginationLabel' => 'Halaman booking saya']) }}
    </section>
</div>
@endsection
