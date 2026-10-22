@extends('layouts.home')
@section('title', 'Bayar pesanan')
@section('description', 'Selesaikan pembayaran booking lapangan dan pantau status verifikasinya di CourtBook.')
@section('content')
<div class="home-wrap booking-flow payment-launch">
    <a class="schedule-back" href="{{ route('bookings.show', $booking) }}">&larr; Kembali ke pesanan</a>
    <x-booking-steps :current="4"/>
    <div class="payment-launch-grid">
        <section class="payment-match-receipt" aria-labelledby="payment-court-name">
            <div class="payment-receipt-brand"><span>Pesanan {{ $booking->code }}</span><x-logo/></div>
            <h2 id="payment-court-name">{{ $booking->court_name }}</h2>
            <dl class="payment-match-time">
                <div><dt>Tanggal bermain</dt><dd>{{ $booking->starts_at->translatedFormat('l, d F Y') }}</dd></div>
                <div><dt>Jam bermain</dt><dd>{{ $booking->starts_at->format('H:i') }}–{{ $booking->ends_at->format('H:i') }} WIB <span>/ {{ $booking->duration }} jam</span></dd></div>
            </dl>
            <div class="payment-launch-amount">
                <span>{{ ['dp' => 'Uang muka (DP)', 'full' => 'Pembayaran penuh', 'balance' => 'Pelunasan sisa tagihan'][$attempt->kind] }}</span>
                <strong>Rp{{ number_format($attempt->amount, 0, ',', '.') }}</strong>
                <small>Nominal pembayaran ini</small>
            </div>
        </section>
        <section class="payment-launch-action" aria-labelledby="payment-launch-title">
            <h1 id="payment-launch-title">Selesaikan<br>pembayaranmu.</h1>
            <p class="payment-launch-intro">Pilih metode pembayaran di jendela berikutnya, lalu ikuti petunjuknya.</p>
            <p class="payment-launch-deadline">Bayar sebelum <strong>{{ $attempt->expires_at->translatedFormat('d M Y, H:i:s') }} WIB</strong>.</p>
            <button class="btn primary wide" type="button" data-pay-token="{{ $attempt->snap_token }}" data-return-url="{{ route('bookings.show', $booking) }}">Pilih metode & bayar <x-home-icon name="arrow"/></button>
            <p class="payment-launch-sandbox">Jendela Midtrans akan terbuka. Pembayaran menggunakan simulasi Sandbox.</p>
            <div class="payment-launch-status" data-payment-watch data-status-url="{{ route('payments.reconcile', $attempt) }}">
                <h2>Status pembayaran</h2>
                <p id="payment-message" data-payment-message role="status" aria-live="polite">Memeriksa status pembayaran…</p>
                <button type="button" class="btn outline" data-payment-retry hidden>Periksa status lagi</button>
            </div>
            <p class="payment-launch-help">Sudah membayar? Tunggu verifikasi dan jangan bayar ulang. Setelah status terverifikasi, kamu otomatis kembali ke pesanan.</p>
            <a class="payment-launch-order-link" href="{{ route('bookings.show', $booking) }}">Lihat status pesanan <x-home-icon name="arrow"/></a>
        </section>
    </div>
</div>
<script src="{{ config('courtbook.midtrans.script_url') }}" data-client-key="{{ config('courtbook.midtrans.client_key') }}" defer></script>
@endsection
