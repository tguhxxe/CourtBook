@extends('layouts.customer')
@section('title','Pesanan '.$booking->code)
@section('content')
@php($canPay = in_array($booking->status, ['held', 'confirmed']) && $booking->remaining())
@php($pendingPayment = $booking->payments->whereIn('status', ['creating', 'uncertain', 'pending'])->sortByDesc('id')->first())
<div class="booking-flow">
    @if($canPay)<x-booking-steps :current="3"/>@endif
    <a class="back-link" href="{{ route('bookings.index') }}">&larr; Booking saya</a>
    <div class="flow-heading"><div><span class="eyebrow">PESANAN {{ $booking->code }}</span><h1>{{ $canPay ? ($booking->paid_amount ? 'Lunasi sisa pembayaran' : 'Periksa pesanan Anda') : 'Detail pesanan Anda' }}</h1><p>{{ $canPay ? 'Pastikan lapangan dan waktunya sudah sesuai, lalu lanjutkan pembayaran.' : 'Lihat jadwal, pembayaran, dan informasi pesanan Anda di sini.' }}</p></div></div>
    @if($booking->status === 'confirmed' && $booking->paid_amount)
    <div class="notice success" role="status">{{ $booking->payment_status === 'paid' ? 'Pembayaran berhasil. Booking sudah lunas.' : 'Pembayaran DP berhasil. Booking dikonfirmasi; silakan lunasi sisa tagihan sebelum batas waktu.' }}</div>
    @endif
    @if($pendingPayment && app(\App\Services\Payments\MidtransGateway::class)->configured())
    <div class="notice" data-payment-watch data-status-url="{{ route('payments.reconcile', $pendingPayment) }}"><p data-payment-message role="status" aria-live="polite">Memeriksa status pembayaran…</p><button type="button" class="btn outline small" data-payment-retry hidden>Periksa status lagi</button></div>
    @endif
    <div class="flow-columns">
        <div class="stack">
            <section class="panel order-summary">
                <span class="eyebrow">JADWAL PESANAN</span><h2>{{ $booking->court_name }}</h2>
                <div class="schedule-summary"><div><small class="muted">Tanggal bermain</small><strong>{{ $booking->starts_at->translatedFormat('l, d F Y') }}</strong></div><div><small class="muted">Jam bermain</small><strong>{{ $booking->starts_at->format('H:i') }}–{{ $booking->ends_at->format('H:i') }} WIB</strong></div><div><small class="muted">Durasi</small><strong>{{ $booking->duration }} jam</strong></div></div>
                <div class="actions"><x-status :value="$booking->status"/><x-status :value="$booking->payment_status"/></div>
                @if($booking->status === 'held')<div class="notice warning">Selesaikan pembayaran sebelum <strong>{{ min($booking->hold_expires_at,$booking->balance_due_at)->translatedFormat('d M Y, H:i:s') }} WIB</strong> agar jadwal ini tidak dilepas.</div>@endif
                @if($booking->cancellation_reason)<div class="notice">{{ $booking->cancellation_reason }}</div>@endif
                <dl class="price-summary"><div><dt>{{ $booking->duration }} jam × Rp{{ number_format($booking->hourly_rate,0,',','.') }}</dt><dd>Rp{{ number_format($booking->total,0,',','.') }}</dd></div><div><dt>Sudah dibayar untuk pesanan ini</dt><dd>Rp{{ number_format($booking->paid_amount,0,',','.') }}</dd></div><div class="total"><dt>Sisa tagihan</dt><dd>Rp{{ number_format($booking->remaining(),0,',','.') }}</dd></div></dl>
                <p class="small-copy muted">Jadwal tidak dapat diubah setelah dipesan. Jika belum membayar dan jadwal salah, batalkan pesanan ini lalu pilih jadwal baru.</p>
            </section>
            @if($booking->refunds->count())
            <section class="panel"><h2>Penanganan refund</h2><a class="text-link" href="{{ route('refunds.index') }}">Lihat refund saya &rarr;</a>@foreach($booking->refunds as $refund)<div class="transaction-item"><div><strong>Rp{{ number_format($refund->amount,0,',','.') }}</strong><p>{{ $refund->reason }}</p><x-status :value="$refund->status"/>@if($refund->admin_notes)<p>{{ $refund->admin_notes }}</p>@endif</div></div>@endforeach<p class="muted">Progres dicatat admin. Refund tidak otomatis.</p></section>
            @endif
        </div>
        <aside class="panel flow-summary">
            @if($canPay)
                <h2>{{ $booking->paid_amount ? 'Pembayaran sisa tagihan' : 'Pilih jumlah pembayaran' }}</h2>
                @if(!app(\App\Services\Payments\MidtransGateway::class)->configured())
                <div class="notice warning">Pembayaran Sandbox belum tersedia. Pengelola perlu mengaktifkan layanan pembayaran. Booking belum dikonfirmasi.</div>
                @else
                <form novalidate action="{{ route('payments.store',$booking) }}" method="post" data-payment-choices>
                    @csrf
                    <fieldset class="payment-options"><legend class="sr-only">Pilihan pembayaran</legend>
                        @if(!$booking->paid_amount)
                        <label class="payment-option"><input type="radio" name="kind" value="dp" data-amount="{{ $booking->dp_amount }}" @checked(old('kind','dp') === 'dp') aria-describedby="kind-help"><span><strong>Bayar uang muka (DP {{ $booking->dp_percent }}%)</strong><b>Rp{{ number_format($booking->dp_amount,0,',','.') }}</b><small>Sisa Rp{{ number_format($booking->total - $booking->dp_amount,0,',','.') }} dibayar sebelum batas pelunasan.</small></span></label>
                        <label class="payment-option"><input type="radio" name="kind" value="full" data-amount="{{ $booking->remaining() }}" @checked(old('kind','dp') === 'full') aria-describedby="kind-help"><span><strong>Bayar langsung lunas</strong><b>Rp{{ number_format($booking->remaining(),0,',','.') }}</b><small>Tidak ada sisa tagihan setelah pembayaran berhasil.</small></span></label>
                        @else
                        <input type="hidden" name="kind" value="balance"><p class="payment-amount">Rp{{ number_format($booking->remaining(),0,',','.') }}</p><p>Uang muka sudah dibayar. Selesaikan sisa tagihan untuk melunasi pesanan ini.</p>
                        @endif
                        <p id="kind-help" class="field-error">{{ $errors->first('kind') }}</p>
                    </fieldset>
                    <p class="small-copy">Batas pelunasan: <strong>{{ $booking->balance_due_at->translatedFormat('d M Y, H:i') }} WIB</strong>. DP hangus jika sisa tagihan tidak dilunasi tepat waktu.</p>
                    <details class="flow-details"><summary>Baca ketentuan pembayaran & pembatalan</summary><x-terms/></details>
                    <label class="check terms-check"><input name="terms" value="1" type="checkbox" required @checked(old('terms')) aria-describedby="terms-error" aria-invalid="{{ $errors->has('terms') ? 'true' : 'false' }}"> Saya memahami ketentuan DP, pelunasan, pembatalan, dan refund.</label>
                    <p id="terms-error" class="field-error">{{ $errors->first('terms') }}</p>
                    <button class="btn primary wide">Lanjut bayar <span data-pay-amount>Rp{{ number_format($booking->paid_amount ? $booking->remaining() : (old('kind','dp') === 'full' ? $booking->remaining() : $booking->dp_amount),0,',','.') }}</span> &rarr;</button>
                    <p class="small-copy muted flow-help">Metode pembayaran dipilih di langkah berikutnya. Pembayaran ini menggunakan simulasi Sandbox.</p>
                </form>
                @endif
            @elseif($booking->status === 'confirmed')
                <span class="eyebrow">PEMBAYARAN SELESAI</span><h2>Siap untuk bermain</h2><p>Pesanan sudah dikonfirmasi dan lunas. Datang sesuai jadwal dan simpan kode pesanan <strong>{{ $booking->code }}</strong>.</p><a class="btn outline wide" href="{{ route('bookings.index') }}">Lihat booking saya</a>
            @else
                <h2>Pesanan tidak aktif</h2><p>Jadwal pada pesanan ini sudah dilepas. Pilih jadwal baru jika Anda ingin bermain.</p><a class="btn primary wide" href="{{ route('courts.index') }}">Pilih jadwal baru</a>
            @endif
        </aside>
    </div>
    <section class="panel flow-secondary">
        <details class="flow-details"><summary>Riwayat pembayaran ({{ $booking->payments->count() }})</summary>
            @forelse($booking->payments as $payment)<div class="transaction-item"><div><strong>Rp{{ number_format($payment->amount,0,',','.') }}</strong> · {{ ['dp'=>'Uang muka (DP)','full'=>'Pembayaran penuh','balance'=>'Pelunasan'][$payment->kind] }}<small class="mono wrap-anywhere">{{ $payment->order_id }}</small><x-status :value="$payment->status"/></div><form novalidate method="post" action="{{ route('payments.reconcile',$payment) }}">@csrf<button class="btn outline small">Periksa status</button></form></div>@empty<p class="muted">Belum ada percobaan pembayaran.</p>@endforelse
        </details>
        @if(!$canPay)<details class="flow-details"><summary>Ketentuan pembayaran & pembatalan</summary><x-terms/></details>@endif
        @if($booking->canCancel() && $booking->paid_amount)
        <x-customer-refund-form :booking="$booking"/>
        @elseif($booking->canCancel())
        <details class="flow-details"><summary>Ingin membatalkan pesanan?</summary><p>Jadwal akan dilepas. Pengembalian dana mengikuti ketentuan pembatalan.</p><form novalidate action="{{ route('bookings.cancel',$booking) }}" method="post" data-confirm="Batalkan {{ $booking->code }}? Slot akan dilepas. Jika memenuhi syarat, refund dicatat untuk penanganan manual." data-action-label="Batalkan booking">@csrf<button class="btn danger-outline">Batalkan booking</button></form></details>
        @elseif(in_array($booking->status,['held','confirmed']) && $booking->paid_amount)<p class="muted small-copy">Pembatalan mandiri tidak tersedia kurang dari 24 jam sebelum jadwal.</p>@endif
    </section>
</div>
@endsection
