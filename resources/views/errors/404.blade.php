@extends('layouts.customer')
@section('title','Halaman tidak ditemukan')
@section('content')<section class="panel empty"><span class="eyebrow">404</span><h1>Halaman tidak ditemukan</h1><p>Halaman atau lapangan ini tidak tersedia.</p><a class="btn primary" href="{{ route('home') }}">Kembali ke beranda</a></section>@endsection
