<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}"><title>@yield('title', 'Operasional') | Admin CourtBook</title>
    <x-theme-assets/>
</head>
<body class="admin-body">
    <a class="skip" href="#main">Lewati ke konten</a>
    <div class="admin-shell">
        <x-admin.sidebar/>
        <div class="admin-content">
            <header class="admin-header"><span>Panel pengelola</span><span>{{ auth()->user()->name }}</span></header>
            <main id="main" class="admin-main" tabindex="-1">@include('components.feedback')@yield('content')</main>
        </div>
    </div>
    @include('components.confirm-dialog')
</body>
</html>
