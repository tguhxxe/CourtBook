@extends('layouts.home')
@section('title', 'Cari lapangan')
@section('description', 'Pilih lapangan tenis atau mini soccer, cek tarif per jam, lalu lihat jadwal bermain. CourtBook, website simulasi praktikum.')
@section('content')
<div class="court-catalog">
    <section class="catalog-intro" aria-labelledby="catalog-title">
        <div class="home-wrap">
            <x-booking-steps :current="1"/>
            <h1 id="catalog-title">Pilih lapanganmu.</h1>
            <p>Tenis atau mini soccer, tentukan tempat mainmu.<br>Pilih lapangan, lalu lihat tanggal dan jam yang tersedia.</p>
        </div>
    </section>
    <section class="home-wrap catalog-results" aria-labelledby="catalog-results-title">
        <form novalidate class="catalog-filters" method="get" action="{{ route('courts.index') }}" aria-label="Cari dan filter lapangan">
            <x-field name="q" label="Nama lapangan" :value="request('q')" placeholder="Cari nama lapangan" maxlength="100"/>
            <div class="field">
                <label for="sport">Jenis olahraga</label>
                <select id="sport" name="sport" aria-invalid="{{ $errors->has('sport') ? 'true' : 'false' }}" aria-describedby="sport-help">
                    <option value="">Semua olahraga</option>
                    <option value="tennis" @selected(request('sport') === 'tennis')>Tenis</option>
                    <option value="soccer" @selected(request('sport') === 'soccer')>Mini soccer</option>
                </select>
                <small id="sport-help" class="field-error">{{ $errors->first('sport') }}</small>
            </div>
            <button type="submit" class="home-button home-button-blue">Cari lapangan</button>
            <a class="catalog-reset" href="{{ route('courts.index') }}" aria-label="Hapus pencarian dan filter">Hapus filter</a>
        </form>
        <div class="catalog-result-heading">
            <h2 id="catalog-results-title">Lapangan tersedia</h2>
            <p>{{ $courts->total() }} lapangan ditemukan</p>
        </div>
        <div class="home-court-grid">
            @forelse($courts as $court)
                <x-home.court-card :court="$court"/>
            @empty
                <div class="catalog-empty">
                    <h3>Lapangan tidak ditemukan</h3>
                    <p>Ubah nama atau jenis olahraga, atau hapus filter untuk melihat semua lapangan.</p>
                    <a class="home-button home-button-blue" href="{{ route('courts.index') }}">Lihat semua lapangan</a>
                </div>
            @endforelse
        </div>
        @if($courts->count())<p class="home-photo-note">Gambar merupakan ilustrasi, bukan foto lokasi sebenarnya.</p>@endif
        {{ $courts->links('components.home.pagination') }}
        <p class="catalog-demo">Website simulasi praktikum. Seluruh tempat dan fasilitas adalah simulasi.</p>
    </section>
</div>
@endsection
