@extends('layouts.customer')
@section('title','Bayar pesanan')
@section('content')
<div class="booking-flow">
    <x-booking-steps :current="4"/>
    <a class="back-link" href="{{ route('bookings.show',$booking) }}">&larr; Kembali ke pesanan</a>
    <div class="panel payment-page guided-payment">
        <span class="eyebrow">LANGKAH TERAKHIR</span><h1>Selesaikan pembayaran</h1>
        <p>Pilih metode pembayaran, lalu ikuti petunjuk yang muncul.</p>
        <div class="payment-receipt"><span>{{ ['dp'=>'Uang muka (DP)','full'=>'Pembayaran penuh','balance'=>'Pelunasan sisa tagihan'][$attempt->kind] }}</span><h2 class="payment-amount">Rp{{ number_format($attempt->amount,0,',','.') }}</h2><p><strong>{{ $booking->court_name }}</strong><br>{{ $booking->starts_at->translatedFormat('d F Y') }} · {{ $booking->starts_at->format('H:i') }}–{{ $booking->ends_at->format('H:i') }} WIB</p></div>
        <button class="btn primary wide" type="button" data-pay-token="{{ $attempt->snap_token }}" data-payment-watch data-status-url="{{ route('payments.reconcile',$attempt) }}" data-return-url="{{ route('bookings.show',$booking) }}">Pilih metode & bayar &rarr;</button>
        <p class="small-copy muted flow-help">Jendela pembayaran Midtrans akan terbuka. Ini pembayaran simulasi Sandbox.</p>
        <div class="notice"><p id="payment-message" data-payment-message role="status" aria-live="polite">Setelah pembayaran berhasil, Anda akan otomatis kembali ke pesanan yang sudah diperbarui.</p><button type="button" class="btn outline" data-payment-retry hidden>Periksa status lagi</button></div>
        <p class="small-copy">Bayar sebelum <strong>{{ $attempt->expires_at->translatedFormat('d M Y, H:i:s') }} WIB</strong>. Jika sudah membayar, jangan bayar ulang saat status masih diperiksa.</p>
        <a class="text-link" href="{{ route('bookings.show',$booking) }}">Lihat status pesanan</a>
    </div>
</div>
<script src="{{ config('courtbook.midtrans.script_url') }}" data-client-key="{{ config('courtbook.midtrans.client_key') }}" defer></script>
@endsection
