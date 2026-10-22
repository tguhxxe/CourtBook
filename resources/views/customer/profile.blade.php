@extends('layouts.customer')
@section('title', 'Profil saya')
@section('content')
<div class="page-heading"><h1>Profil saya.</h1><p class="muted">Perbarui informasi akun dan jaga akses ke jadwal bermainmu.</p></div>
<form novalidate method="post" action="{{ route('profile.update') }}" data-dirty class="profile-editor">
    @csrf @method('PUT')
    <div class="account-form-grid">
        <section class="panel">
            <h2>Informasi akun</h2>
            <x-field name="name" label="Nama lengkap" :value="$user->name" autocomplete="name" required/>
            <x-field name="email" label="Email" type="email" :value="$user->email" autocomplete="email" required/>
            <x-field name="phone" label="Nomor telepon" type="tel" :value="$user->phone" autocomplete="tel" hint="Opsional. 8–25 karakter, angka dan tanda + ( ) - diperbolehkan."/>
        </section>
        <section class="panel">
            <h2>Ubah kata sandi</h2><p class="muted">Kosongkan bagian ini jika tidak ingin mengganti kata sandi.</p>
            <x-field name="current_password" label="Kata sandi saat ini" type="password" autocomplete="current-password"/>
            <x-field name="password" label="Kata sandi baru" type="password" autocomplete="new-password" hint="Minimal 8 karakter."/>
            <x-field name="password_confirmation" label="Konfirmasi kata sandi baru" type="password" autocomplete="new-password"/>
        </section>
    </div>
    <button class="btn primary" type="submit">Simpan profil</button>
</form>
@endsection
