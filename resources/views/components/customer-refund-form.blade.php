@props(['booking'])
<section class="panel">
    <span class="eyebrow">PENGEMBALIAN DANA</span><h2>Ajukan refund</h2>
    <p>Batalkan booking ini dan ajukan pengembalian dana <strong>Rp{{ number_format($booking->paid_amount,0,',','.') }}</strong> yang sudah dibayar. Admin akan meninjau permintaan Anda.</p>
    <p class="small-copy muted">Tersedia minimal 24 jam sebelum jadwal. Pengajuan melepaskan jadwal booking. Refund ditangani manual oleh admin.</p>
    <form novalidate action="{{ route('refunds.store', $booking) }}" method="post" data-confirm="Ajukan refund dan batalkan {{ $booking->code }}? Jadwal akan dilepas dan refund Rp{{ number_format($booking->paid_amount,0,',','.') }} akan ditinjau admin." data-action-label="Ajukan refund & batalkan">
        @csrf
        <x-field name="reason" label="Alasan refund" required minlength="5" maxlength="200" hint="Tulis alasan pembatalan (5?200 karakter). Jangan isi nomor rekening, kredensial, atau data kartu."/>
        <button class="btn danger-outline" type="submit">Ajukan refund & batalkan booking</button>
    </form>
</section>
