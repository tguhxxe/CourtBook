@props(['home' => false])
<link rel="preload" href="{{ Vite::asset('resources/fonts/Manrope-Variable.ttf') }}" as="font" type="font/ttf" crossorigin>
<link rel="preload" href="{{ Vite::asset('resources/fonts/BarlowCondensed-Bold.ttf') }}" as="font" type="font/ttf" crossorigin>
@if($home)
    @vite(['resources/css/app.css', 'resources/css/home.css', 'resources/js/app.js'])
@else
    @vite(['resources/css/app.css', 'resources/js/app.js'])
@endif
