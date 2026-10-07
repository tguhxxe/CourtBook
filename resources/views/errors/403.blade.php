@extends('layouts.customer')
@section('title','Akses tidak tersedia')
@section('content')<section class="panel empty"><span class="eyebrow">403</span><h1>Akses tidak tersedia</h1><p>Akun Anda tidak memiliki akses ke halaman ini.</p><a class="btn primary" href="{{ route('home') }}">Kembali ke beranda</a></section>@endsection
