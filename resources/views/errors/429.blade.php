@extends('layouts.customer')
@section('title','Terlalu banyak percobaan')
@section('content')<section class="panel empty"><span class="eyebrow">429</span><h1>Terlalu banyak percobaan</h1><p>Tunggu sebentar sebelum mencoba kembali.</p><a class="btn primary" href="{{ route('home') }}">Kembali ke beranda</a></section>@endsection
