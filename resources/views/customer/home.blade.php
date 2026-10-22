@extends('layouts.home')
@section('title', 'Waktunya main di lapangan')
@section('content')
<section class="home-hero" aria-labelledby="home-title">
    <img class="home-hero-image" src="{{ asset('images/home/blue-tennis-hero.webp') }}" width="1942" height="809" alt="" fetchpriority="high">
    <div class="home-hero-shade" aria-hidden="true"></div>
    <div class="home-wrap home-hero-inner">
        <div class="home-hero-copy">
            <p class="home-location"><x-home-icon name="pin"/> Tenis & mini soccer di Malang</p>
            <h1 id="home-title">Waktunya main.<br>Di lapangan.</h1>
            <p class="home-hero-description">Pilih lapangan dan waktu yang pas. Pesan online untuk sesi tenis atau mini soccer berikutnya.</p>
            <div class="home-hero-actions">
                <a class="home-button home-button-light" href="{{ route('courts.index') }}">Cari lapangan <x-home-icon name="arrow"/></a>
                <a class="home-hero-guide" href="#cara-booking">Lihat cara booking <x-home-icon name="chevron"/></a>
            </div>
            <p class="home-hero-note"><x-home-icon name="check"/> Lihat jadwal tanpa perlu masuk</p>
        </div>
        <span class="home-image-caption">Ilustrasi olahraga</span>
    </div>
</section>

<div class="home-quick-info">
    <div class="home-wrap home-info-grid">
        <div class="home-info-item"><x-home-icon name="clock"/><div><span>Jam operasional</span><strong>{{ sprintf('%02d.00', $settings->open_hour) }}–{{ sprintf('%02d.00', $settings->close_hour) }} WIB</strong></div></div>
        <div class="home-info-item"><x-home-icon name="calendar"/><div><span>Sesuaikan waktu main</span><strong>Booking 1–{{ config('courtbook.max_duration') }} jam</strong></div></div>
        <div class="home-info-item"><x-home-icon name="wallet"/><div><span>Pilihan pembayaran</span><strong>DP {{ $settings->dp_percent }}% atau lunas</strong></div></div>
    </div>
</div>

<section class="home-wrap home-section home-courts" aria-labelledby="home-courts-title">
    <div class="home-section-heading">
        <div><h2 id="home-courts-title">Pilih lapanganmu.</h2><p>Temukan lapangan pilihanmu, lalu cek waktu bermain yang pas.</p></div>
        <a class="home-inline-link" href="{{ route('courts.index') }}">Lihat semua lapangan <x-home-icon name="arrow"/></a>
    </div>
    <div class="home-court-grid">
        @forelse($courts as $court)
            <x-home.court-card :court="$court"/>
        @empty
            <div class="home-empty"><h3>Lapangan sedang disiapkan.</h3><p>Belum ada lapangan yang bisa ditampilkan. Silakan cek kembali nanti.</p><a class="home-inline-link" href="{{ route('courts.index') }}">Buka daftar lapangan <x-home-icon name="arrow"/></a></div>
        @endforelse
    </div>
    <p class="home-photo-note">Gambar merupakan ilustrasi, bukan foto lokasi sebenarnya.</p>
</section>

<x-home.booking-guide :settings="$settings"/>

<section class="home-wrap home-section home-faq" aria-labelledby="home-faq-title">
    <div class="home-faq-intro"><h2 id="home-faq-title">Sebelum mulai main.</h2><p>Beberapa hal yang perlu kamu tahu sebelum memesan lapangan.</p></div>
    <div class="home-faq-list">
        <details><summary>Perlu akun untuk melihat jadwal? <x-home-icon name="plus"/></summary><div class="home-faq-answer"><p>Tidak. Lapangan dan jadwal dapat dilihat tanpa masuk. Kamu baru perlu masuk atau membuat akun saat ingin memesan.</p></div></details>
        <details><summary>Apakah harus langsung bayar lunas? <x-home-icon name="plus"/></summary><div class="home-faq-answer"><p>Kamu bisa membayar DP {{ $settings->dp_percent }}% atau langsung lunas. Booking dikonfirmasi setelah pembayaran terverifikasi. Jika memilih DP, sisa tagihan harus lunas paling lambat {{ config('courtbook.lead_hours') }} jam sebelum bermain.</p></div></details>
        <details><summary>Bagaimana ketentuan pembatalannya? <x-home-icon name="plus"/></summary><div class="home-faq-answer"><x-terms/></div></details>
    </div>
</section>

<aside class="home-wrap home-demo" aria-label="Informasi simulasi"><span class="home-demo-label">Data simulasi</span><p>Website praktikum. Tempat dan fasilitas merupakan contoh; pembayaran menggunakan Sandbox. Tema masih membutuhkan persetujuan asisten.</p></aside>
@endsection
