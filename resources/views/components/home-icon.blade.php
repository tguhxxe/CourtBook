@props(['name'])
<svg {{ $attributes }} width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    @switch($name)
        @case('arrow')<path d="M5 12h14m-6-6 6 6-6 6"/>@break
        @case('clock')<circle cx="12" cy="12" r="8.5"/><path d="M12 7v5l3 2"/>@break
        @case('calendar')<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M7 3v4m10-4v4M3 11h18m-13 4h2m4 0h2"/>@break
        @case('wallet')<rect x="3" y="5" width="18" height="15" rx="2"/><path d="M3 9h18m-5 5h5"/>@break
        @case('pin')<path d="M19 10c0 5-7 11-7 11S5 15 5 10a7 7 0 1 1 14 0Z"/><circle cx="12" cy="10" r="2.3"/>@break
        @case('check')<path d="m5 12 4 4L19 6"/>@break
        @case('chevron')<path d="m7 10 5 5 5-5"/>@break
        @case('menu')<path d="M4 7h16M4 12h16M4 17h16"/>@break
        @case('plus')<path d="M5 12h14M12 5v14"/>@break
        @case('tennis')<ellipse cx="14" cy="9" rx="5.5" ry="7" transform="rotate(35 14 9)"/><path d="m10 15-6 7m-1-1 3 2M12 3l7 7M9 6l8 8M9 10l7-7m-5 11 8-8"/>@break
        @case('soccer')<circle cx="12" cy="12" r="9"/><path d="m12 7 5 4-2 6H9l-2-6 5-4Zm0-4v4m9 4h-4m0 8-2-2m-8 2 2-2m-6-6h4"/>@break
    @endswitch
</svg>
