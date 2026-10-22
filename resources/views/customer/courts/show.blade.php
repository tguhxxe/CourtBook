@extends('layouts.home')
@section('title', 'Pilih jadwal · '.$court->name)
@section('description', 'Pilih tanggal, durasi, dan jam bermain untuk lapanganmu di CourtBook. Website simulasi praktikum.')
@section('content')
<div class="home-wrap booking-flow court-schedule">
    <a class="schedule-back" href="{{ route('courts.index') }}">&larr; Pilih lapangan lain</a>
    <header class="schedule-heading">
        <div class="schedule-heading-copy">
            <span class="schedule-sport">{{ $court->sport === 'tennis' ? 'Tenis' : 'Mini soccer' }}</span>
            <h1>{{ $court->name }}</h1>
            <p class="schedule-tariff">Rp{{ number_format($court->hourly_rate, 0, ',', '.') }} <span>/ jam</span></p>
            <p>Temukan waktu bermain untuk timmu.</p>
        </div>
        <figure class="schedule-court-photo">
            <img src="{{ asset('images/home/'.($court->sport === 'soccer' ? 'mini-soccer' : 'blue-tennis-court').'.webp') }}" width="1536" height="1024" alt="" fetchpriority="high">
            <figcaption>Ilustrasi, bukan foto lokasi sebenarnya.</figcaption>
        </figure>
    </header>
    <x-booking-steps :current="1"/>
    <div class="schedule-sheet">
        <h2 class="schedule-sheet-title">Atur jadwal bermain.</h2>
        <section class="date-panel" aria-label="Pilih tanggal bermain">
            <form novalidate class="date-filter" method="get" data-schedule-date>
                <x-field name="date" label="Tanggal bermain" type="date" :value="$date" :min="now()->toDateString()" :max="now()->addDays(30)->toDateString()" required/>
                <button type="submit" class="btn outline">Lihat jadwal</button>
            </form>
            <div class="date-shortcuts">
                <a href="{{ route('courts.show', [$court, 'date' => now()->toDateString()]) }}" @if($date === now()->toDateString()) aria-current="date" @endif>Hari ini</a>
                <a href="{{ route('courts.show', [$court, 'date' => now()->addDay()->toDateString()]) }}" @if($date === now()->addDay()->toDateString()) aria-current="date" @endif>Besok</a>
                <span>Buka {{ sprintf('%02d.00–%02d.00', $settings->open_hour, $settings->close_hour) }} WIB</span>
            </div>
        </section>
        <form novalidate action="{{ route('bookings.store') }}" method="post" data-booking data-rate="{{ $court->hourly_rate }}" data-dp="{{ $settings->dp_percent }}" data-close-hour="{{ $settings->close_hour }}">
            @csrf
            <input type="hidden" name="court_id" value="{{ $court->id }}">
            <input type="hidden" name="date" value="{{ $date }}">
            <section class="schedule-panel" aria-label="Pilih durasi dan jam bermain">
                <div class="schedule-duration-row">
                    <div class="field duration-field">
                        <label for="duration">Lama bermain</label>
                        <select name="duration" id="duration" aria-invalid="{{ $errors->has('duration') ? 'true' : 'false' }}" aria-describedby="duration-help">
                            @for($d = 1; $d <= 4; $d++)<option value="{{ $d }}" @selected(old('duration', 1) == $d)>{{ $d }} jam</option>@endfor
                        </select>
                        <small id="duration-help" class="field-error">{{ $errors->first('duration') }}</small>
                    </div>
                    <p>Jadwal <strong>{{ \Carbon\Carbon::parse($date)->translatedFormat('l, d F Y') }}</strong><br>Semua jam dalam WIB.</p>
                </div>
                <fieldset class="time-options">
                    <legend>Pilih jam mulai</legend>
                    <p id="hour-hint">Pilih jam yang bertanda tersedia. Jam lainnya tidak dapat dipilih.</p>
                    <div class="slot-grid">
                        @for($h = $settings->open_hour; $h < $settings->close_hour; $h++)
                            @php($reserved = $slots->get(sprintf('%02d', $h)))
                            @php($tooSoon = \Carbon\Carbon::parse($date.' '.sprintf('%02d:00', $h))->lt(now()->addHours(config('courtbook.lead_hours'))))
                            <label class="time-choice">
                                <input type="radio" name="hour" value="{{ $h }}" data-slot="{{ $h }}" data-unavailable="{{ ($reserved || $tooSoon) ? 'true' : 'false' }}" @disabled($reserved || $tooSoon) @checked(old('hour') == $h) required aria-describedby="hour-help hour-hint" aria-invalid="{{ $errors->has('hour') ? 'true' : 'false' }}">
                                <span @class(['slot', 'maintenance' => (bool) $reserved?->maintenance_id])><strong>{{ sprintf('%02d.00', $h) }}</strong><small data-slot-label>{{ $reserved ? ($reserved->maintenance_id ? 'Maintenance' : 'Terisi') : ($tooSoon ? 'Lewat batas' : 'Tersedia') }}</small></span>
                            </label>
                        @endfor
                    </div>
                    <p id="hour-help" class="field-error">{{ $errors->first('hour') }}</p>
                    <p data-no-slots class="notice warning" hidden>Tidak ada jam yang tersedia untuk durasi ini. Coba durasi lebih singkat atau pilih tanggal lain.</p>
                </fieldset>
                <p class="schedule-lead-note">Pesan minimal {{ config('courtbook.lead_hours') }} jam sebelum mulai. Seluruh durasi bermain harus kosong.</p>
            </section>
            <section class="flow-summary" aria-labelledby="schedule-summary-title">
                <div class="schedule-summary-selection">
                    <h2 id="schedule-summary-title">Ringkasan jadwal</h2>
                    <p class="chosen-time" data-time-summary role="status">Pilih salah satu jam yang tersedia untuk melanjutkan.</p>
                </div>
                <div class="schedule-summary-bottom">
                    <div class="schedule-summary-amount">
                        <span>Total sewa</span><strong data-price-total>Rp{{ number_format($court->hourly_rate, 0, ',', '.') }}</strong>
                    </div>
                    <div class="schedule-summary-deposit">
                        <p>DP {{ $settings->dp_percent }}% <strong data-price-dp>Rp{{ number_format((int) ceil($court->hourly_rate * $settings->dp_percent / 100), 0, ',', '.') }}</strong></p>
                        <p>Sisa setelah DP <span data-price-balance>Rp{{ number_format($court->hourly_rate - (int) ceil($court->hourly_rate * $settings->dp_percent / 100), 0, ',', '.') }}</span></p>
                        <small>Bisa pilih DP atau lunas saat periksa pesanan.</small>
                    </div>
                    <div class="schedule-summary-action">
                        @auth
                            @if(auth()->user()->role === 'customer')<button type="submit" class="btn primary wide" data-booking-continue>Lanjut: periksa pesanan &rarr;</button>@else<p class="notice">Booking dilakukan melalui akun pelanggan.</p>@endif
                        @else<a class="btn primary wide" href="{{ route('login') }}">Masuk untuk memesan &rarr;</a>@endauth
                    </div>
                </div>
                <p class="flow-help">Belum ada pembayaran. Setelah lanjut, jadwal ditahan maksimal {{ config('courtbook.hold_minutes') }} menit untuk pembayaran. Jadwal yang sudah dipesan tidak dapat diubah.</p>
            </section>
        </form>
        <details class="flow-details"><summary>Tentang {{ $court->name }}</summary><p>{{ $court->description }}</p><p>{{ implode(' · ', $court->facilities) }}</p><p>Lapangan, tarif, dan fasilitas adalah simulasi.</p></details>
    </div>
</div>
@endsection
