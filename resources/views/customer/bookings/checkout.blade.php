@extends('layouts.home')
@section('title', 'Pesanan '.$booking->code)
@section('description', 'Periksa jadwal, status pesanan, dan pembayaran booking lapanganmu di CourtBook.')
@section('content')
@php($canPay = in_array($booking->status, ['held', 'confirmed']) && $booking->remaining())
@php($courtIllustration = $booking->court?->sport === 'soccer' ? 'mini-soccer' : 'blue-tennis-court')
@php($pendingPayment = $booking->payments->whereIn('status', ['creating', 'uncertain', 'pending'])->sortByDesc('id')->first())
<div class="home-wrap booking-flow order-detail">
    <a class="schedule-back" href="{{ route('bookings.index') }}">&larr; Booking saya</a>
    <header class="order-heading">
        <div>
            <p class="order-code">Pesanan {{ $booking->code }}</p>
            <h1>{{ $canPay ? ($booking->paid_amount ? 'Lunasi pesananmu.' : 'Periksa pesananmu.') : 'Detail pesananmu.' }}</h1>
            <p>{{ $canPay ? 'Periksa jadwal dan tagihan, lalu lanjutkan pembayaran.' : 'Jadwal, status, dan catatan pembayaran pesananmu.' }}</p>
        </div>
        <figure><img src="{{ asset('images/home/'.$courtIllustration.'.webp') }}" width="1536" height="1024" alt="" fetchpriority="high"><figcaption>Ilustrasi olahraga</figcaption></figure>
    </header>
    @if($canPay)<x-booking-steps :current="3"/>@endif
    <div class="order-sheet">
    @if($booking->status === 'confirmed' && $booking->paid_amount)
    <div class="notice success" role="status">{{ $booking->payment_status === 'paid' ? 'Pembayaran berhasil. Booking sudah lunas.' : 'Pembayaran DP berhasil. Booking dikonfirmasi; silakan lunasi sisa tagihan sebelum batas waktu.' }}</div>
    @endif
    @if($pendingPayment && app(\App\Services\Payments\MidtransGateway::class)->configured())
    <div class="notice" data-payment-watch data-status-url="{{ route('payments.reconcile', $pendingPayment) }}"><p data-payment-message role="status" aria-live="polite">Memeriksa status pembayaran…</p><button type="button" class="btn outline small" data-payment-retry hidden>Periksa status lagi</button></div>
    @endif
            <section class="panel order-summary">
                <h2>{{ $booking->court_name }}</h2>
                <div class="schedule-summary"><div><small class="muted">Tanggal bermain</small><strong>{{ $booking->starts_at->translatedFormat('l, d F Y') }}</strong></div><div><small class="muted">Jam bermain</small><strong>{{ $booking->starts_at->format('H:i') }}–{{ $booking->ends_at->format('H:i') }} WIB</strong></div><div><small class="muted">Durasi</small><strong>{{ $booking->duration }} jam</strong></div></div>
                <div class="actions"><x-status :value="$booking->status"/><x-status :value="$booking->payment_status"/></div>
                @if($booking->status === 'held')<div class="notice warning">Selesaikan pembayaran sebelum <strong>{{ min($booking->hold_expires_at,$booking->balance_due_at)->translatedFormat('d M Y, H:i:s') }} WIB</strong> agar jadwal ini tidak dilepas.</div>@endif
                @if($booking->cancellation_reason)<div class="notice">{{ $booking->cancellation_reason }}</div>@endif
                <dl class="price-summary"><div><dt>{{ $booking->duration }} jam × Rp{{ number_format($booking->hourly_rate,0,',','.') }}</dt><dd>Rp{{ number_format($booking->total,0,',','.') }}</dd></div><div><dt>Sudah dibayar untuk pesanan ini</dt><dd>Rp{{ number_format($booking->paid_amount,0,',','.') }}</dd></div><div class="total"><dt>{{ in_array($booking->status, ['held', 'confirmed']) ? 'Sisa tagihan' : 'Nominal belum dibayar' }}</dt><dd>Rp{{ number_format($booking->remaining(),0,',','.') }}</dd></div></dl>
                <p class="order-change-note">Jadwal tidak dapat diubah setelah dipesan.@if($booking->status === 'held') Jika jadwal salah, batalkan pesanan ini lalu pilih jadwal baru.@endif</p>
            </section>
        <aside class="panel flow-summary">
            @if($canPay)
                <h2>{{ $booking->paid_amount ? 'Pembayaran sisa tagihan' : 'Pilih jumlah pembayaran' }}</h2>
                @if(!app(\App\Services\Payments\MidtransGateway::class)->configured())
                <div class="notice warning">Pembayaran Sandbox belum tersedia. Pengelola perlu mengaktifkan layanan pembayaran.@if($booking->status === 'held') Booking belum dikonfirmasi.@endif</div>
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
                <h2>Siap untuk bermain</h2><p>Pesanan sudah dikonfirmasi dan lunas. Datang sesuai jadwal dan simpan kode pesanan <strong>{{ $booking->code }}</strong>.</p><a class="btn outline wide" href="{{ route('bookings.index') }}">Lihat booking saya</a>
            @else
                <h2>Pesanan tidak aktif</h2><p>Jadwal pada pesanan ini sudah dilepas. Pilih jadwal baru jika Anda ingin bermain.</p><a class="btn primary wide" href="{{ route('courts.index') }}">Pilih jadwal baru</a>
            @endif
        </aside>            @if($booking->refunds->count())
            <section class="panel"><h2>Penanganan refund</h2><a class="text-link" href="{{ route('refunds.index') }}">Lihat refund saya &rarr;</a>@foreach($booking->refunds as $refund)<div class="transaction-item"><div><strong>Rp{{ number_format($refund->amount,0,',','.') }}</strong><p>{{ $refund->reason }}</p><x-status :value="$refund->status"/>@if($refund->admin_notes)<p>{{ $refund->admin_notes }}</p>@endif</div></div>@endforeach<p class="muted">Progres dicatat admin. Refund tidak otomatis.</p></section>
            @endif
    <section class="panel flow-secondary">
        <details class="flow-details"><summary>Riwayat pembayaran ({{ $booking->payments->count() }})</summary>
            @forelse($booking->payments as $payment)<div class="transaction-item"><div><strong>Rp{{ number_format($payment->amount,0,',','.') }}</strong> · {{ ['dp'=>'Uang muka (DP)','full'=>'Pembayaran penuh','balance'=>'Pelunasan'][$payment->kind] }}<small class="mono wrap-anywhere">{{ $payment->order_id }}</small><x-status :value="$payment->status"/></div><form novalidate method="post" action="{{ route('payments.reconcile',$payment) }}">@csrf<button class="btn outline small">Periksa status</button></form></div>@empty<p class="muted">Belum ada percobaan pembayaran.</p>@endforelse
        </details>
        @if(!$canPay)<details class="flow-details"><summary>Ketentuan pembayaran & pembatalan</summary><x-terms/></details>@endif
        @if($booking->canCancel() && $booking->paid_amount)
        <details class="flow-details" @if($errors->has('reason')) open @endif><summary>Ingin membatalkan dan mengajukan refund?</summary><x-customer-refund-form :booking="$booking"/></details>
        @elseif($booking->canCancel())
        <details class="flow-details"><summary>Ingin membatalkan pesanan?</summary><p>Jadwal akan dilepas. Pengembalian dana mengikuti ketentuan pembatalan.</p><form novalidate action="{{ route('bookings.cancel',$booking) }}" method="post" data-confirm="Batalkan {{ $booking->code }}? Slot akan dilepas. Jika memenuhi syarat, refund dicatat untuk penanganan manual." data-action-label="Batalkan booking">@csrf<button class="btn danger-outline">Batalkan booking</button></form></details>
        @elseif(in_array($booking->status,['held','confirmed']) && $booking->paid_amount)<p class="muted small-copy">Pembatalan mandiri tidak tersedia kurang dari 24 jam sebelum jadwal.</p>@endif
    </section>    </div>
</div>
@endsection
