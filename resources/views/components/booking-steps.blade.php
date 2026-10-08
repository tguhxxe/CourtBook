@props(['current' => 1])
<nav class="booking-steps" aria-label="Tahapan pemesanan">
    <ol>
        @foreach(['Pilih lapangan', 'Pilih jadwal', 'Periksa pesanan', 'Bayar'] as $label)
        <li @class(['is-current' => $loop->iteration === (int) $current, 'is-complete' => $loop->iteration < (int) $current]) @if($loop->iteration === (int) $current) aria-current="step" @endif>
            <span class="step-number" aria-hidden="true">{{ $loop->iteration }}</span><span>{{ $label }}</span>
        </li>
        @endforeach
    </ol>
</nav>
