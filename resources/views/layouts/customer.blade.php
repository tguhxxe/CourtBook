<!doctype html>
<html lang="id" class="home-theme">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}"><title>@yield('title', 'CourtBook') | CourtBook</title>
    <x-theme-assets home/>
</head>
<body class="home-page">
    <a class="skip" href="#main">Lewati ke konten</a>
    <x-home.navbar/>
    <main id="main" class="home-wrap account-workspace" tabindex="-1">@include('components.feedback')@yield('content')</main>
    <x-home.footer/>
    @include('components.confirm-dialog')
</body>
</html>
