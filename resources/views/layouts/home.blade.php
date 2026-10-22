<!doctype html>
<html lang="id" class="home-theme">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="@yield('description', 'Temukan lapangan tenis dan mini soccer, lihat jadwal, lalu pesan waktu bermainmu di CourtBook. Website simulasi praktikum.')">
    <title>@yield('title', 'Waktunya main') | CourtBook</title>
    <x-theme-assets home/>
</head>
<body class="home-page">
    <a class="skip" href="#main">Lewati ke konten</a>
    <x-home.navbar/>
    <main id="main" tabindex="-1">
        @unless($inlineFeedback ?? false)
            <div class="home-wrap home-feedback">@include('components.feedback')</div>
        @endunless
        @yield('content')
    </main>
    <x-home.footer/>
    @include('components.confirm-dialog')
</body>
</html>
