@extends('layouts.customer')
@section('title','Refund saya')
@section('content')
<div class="page-heading row-heading"><div><span class="eyebrow">PENGEMBALIAN DANA</span><h1>Refund saya</h1><p class="muted">Pantau pengajuan refund dan catatan penanganan dari admin.</p></div><a class="btn outline" href="{{ route('bookings.index') }}">Lihat booking saya &rarr;</a></div>
<div class="notice"><p>Untuk mengajukan refund, buka detail booking yang sudah dibayar lalu pilih <strong>Ajukan refund</strong>. Pembatalan berbayar tersedia minimal 24 jam sebelum jadwal. Pengajuan membatalkan booking dan melepaskan jadwal.</p><p>Refund ditinjau dan ditangani admin secara manual. Status penanganan selesai mengikuti catatan admin; pengajuan ini tidak langsung mengirim dana.</p></div>
<div class="stack">
@forelse($refunds as $refund)
<section class="panel">
    <div class="section-heading"><div><a class="text-link mono" href="{{ route('bookings.show', $refund->booking) }}">{{ $refund->booking->code }}</a><h2>Rp{{ number_format($refund->amount,0,',','.') }}</h2><p class="muted">{{ $refund->booking->court_name }}</p></div><x-status :value="$refund->status"/></div>
    <p><strong>Alasan:</strong> {{ $refund->reason }}</p>
    <p class="small-copy muted">Diajukan {{ $refund->created_at->translatedFormat('d M Y, H:i') }} WIB &middot; Diperbarui {{ $refund->updated_at->translatedFormat('d M Y, H:i') }} WIB</p>
    @if($refund->admin_notes)<div class="notice"><strong>Catatan admin</strong><p>{{ $refund->admin_notes }}</p></div>@else<p class="muted">Belum ada catatan penanganan dari admin.</p>@endif
    <a class="text-link" href="{{ route('bookings.show', $refund->booking) }}">Lihat detail booking &rarr;</a>
</section>
@empty
<div class="panel empty"><h2>Belum ada pengajuan refund</h2><p>Pengajuan Anda akan muncul di sini. Refund yang dibuat otomatis untuk pembayaran terlambat juga dapat dipantau di halaman ini.</p><a class="btn primary" href="{{ route('bookings.index') }}">Lihat booking saya</a></div>
@endforelse
</div>
{{ $refunds->links() }}
@endsection
