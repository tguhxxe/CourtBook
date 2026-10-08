@extends('layouts.customer')
@section('title','Pilih jadwal · '.$court->name)
@section('content')
<div class="booking-flow">
    <x-booking-steps :current="2"/>
    <a class="back-link" href="{{ route('courts.index') }}">&larr; Pilih lapangan lain</a>
    <div class="flow-heading"><div><span class="eyebrow">{{ $court->sport === 'tennis' ? 'TENIS' : 'MINI SOCCER' }}</span><h1>Pilih waktu bermain</h1><p>{{ $court->name }} · <strong>Rp{{ number_format($court->hourly_rate,0,',','.') }} / jam</strong></p></div></div>
    <section class="panel date-panel" aria-labelledby="date-heading">
        <h2 id="date-heading">Tanggal berapa?</h2>
        <form novalidate class="date-filter" method="get" data-schedule-date>
            <x-field name="date" label="Tanggal bermain" type="date" :value="$date" :min="now()->toDateString()" :max="now()->addDays(30)->toDateString()" required/>
            <button class="btn outline">Lihat jadwal</button>
        </form>
        <div class="date-shortcuts"><a href="{{ route('courts.show', [$court, 'date' => now()->toDateString()]) }}" @if($date === now()->toDateString()) aria-current="date" @endif>Hari ini</a><a href="{{ route('courts.show', [$court, 'date' => now()->addDay()->toDateString()]) }}" @if($date === now()->addDay()->toDateString()) aria-current="date" @endif>Besok</a><span>Jam operasional {{ sprintf('%02d.00–%02d.00',$settings->open_hour,$settings->close_hour) }} WIB</span></div>
    </section>
    <form novalidate action="{{ route('bookings.store') }}" method="post" data-booking data-rate="{{ $court->hourly_rate }}" data-dp="{{ $settings->dp_percent }}" data-close-hour="{{ $settings->close_hour }}" class="flow-columns">
        @csrf
        <input type="hidden" name="court_id" value="{{ $court->id }}"><input type="hidden" name="date" value="{{ $date }}">
        <section class="panel schedule-panel">
            <h2>Berapa lama dan mulai jam berapa?</h2>
            <p class="muted">Jadwal untuk <strong>{{ \Carbon\Carbon::parse($date)->translatedFormat('l, d F Y') }}</strong>. Semua jam dalam WIB.</p>
            <div class="field duration-field"><label for="duration">Lama bermain</label><select name="duration" id="duration" aria-invalid="{{ $errors->has('duration') ? 'true' : 'false' }}" aria-describedby="duration-help">@for($d=1;$d<=4;$d++)<option value="{{ $d }}" @selected(old('duration',1)==$d)>{{ $d }} jam</option>@endfor</select><small id="duration-help" class="field-error">{{ $errors->first('duration') }}</small></div>
            <fieldset class="time-options"><legend>Pilih jam mulai</legend><p class="small-copy muted" id="hour-hint">Klik salah satu jam yang tersedia. Jam abu-abu tidak dapat dipilih.</p>
                <div class="slot-grid">
                    @for($h=$settings->open_hour;$h<$settings->close_hour;$h++)
                    @php($reserved=$slots->get(sprintf('%02d',$h)))
                    @php($tooSoon=\Carbon\Carbon::parse($date.' '.sprintf('%02d:00',$h))->lt(now()->addHours(config('courtbook.lead_hours'))))
                    <label class="time-choice">
                        <input type="radio" name="hour" value="{{ $h }}" data-slot="{{ $h }}" data-unavailable="{{ ($reserved || $tooSoon) ? 'true' : 'false' }}" @disabled($reserved || $tooSoon) @checked(old('hour') == $h) required aria-describedby="hour-help hour-hint" aria-invalid="{{ $errors->has('hour') ? 'true' : 'false' }}">
                        <span class="slot"><strong>{{ sprintf('%02d.00',$h) }}</strong><small data-slot-label>{{ $reserved ? ($reserved->maintenance_id ? 'Maintenance' : 'Terisi') : ($tooSoon ? 'Lewat batas' : 'Tersedia') }}</small></span>
                    </label>
                    @endfor
                </div>
                <p id="hour-help" class="field-error">{{ $errors->first('hour') }}</p>
                <p data-no-slots class="notice warning" hidden>Tidak ada jam yang tersedia untuk durasi ini. Coba durasi lebih singkat atau pilih tanggal lain.</p>
            </fieldset>
            <p class="small-copy muted">Pemesanan minimal {{ config('courtbook.lead_hours') }} jam sebelum mulai. Seluruh durasi bermain harus kosong.</p>
            <details class="flow-details"><summary>Tentang {{ $court->name }}</summary><p>{{ $court->description }}</p><p>{{ implode(' · ', $court->facilities) }}</p><p class="small-copy muted">Lapangan, tarif, dan fasilitas adalah simulasi.</p></details>
        </section>
        <aside class="panel flow-summary">
            <span class="eyebrow">PILIHAN ANDA</span><h2>Ringkasan jadwal</h2><p><strong>{{ $court->name }}</strong><br>{{ \Carbon\Carbon::parse($date)->translatedFormat('d F Y') }}</p>
            <p class="chosen-time" data-time-summary role="status">Pilih jam mulai di sebelah kiri.</p>
            <dl class="price-summary"><div class="total"><dt>Total sewa</dt><dd data-price-total>Rp{{ number_format($court->hourly_rate,0,',','.') }}</dd></div></dl>
            <p class="muted">Pada langkah berikutnya, Anda bisa membayar uang muka (DP) atau langsung lunas.</p>
            <p class="small-copy">DP {{ $settings->dp_percent }}%: <strong data-price-dp>Rp{{ number_format((int)ceil($court->hourly_rate*$settings->dp_percent/100),0,',','.') }}</strong><br>Sisa setelah DP: <span data-price-balance>Rp{{ number_format($court->hourly_rate-(int)ceil($court->hourly_rate*$settings->dp_percent/100),0,',','.') }}</span></p>
            @auth
                @if(auth()->user()->role === 'customer')<button class="btn primary wide" data-booking-continue>Lanjut: periksa pesanan &rarr;</button>@else<p class="notice">Booking dilakukan melalui akun pelanggan.</p>@endif
            @else<a class="btn primary wide" href="{{ route('login') }}">Masuk untuk memesan &rarr;</a>@endauth
            <p class="small-copy muted flow-help">Belum ada pembayaran pada langkah ini. Setelah lanjut, jadwal ditahan maksimal 15 menit untuk pembayaran. Jadwal yang sudah dipesan tidak dapat diubah.</p>
        </aside>
    </form>
</div>
@endsection
