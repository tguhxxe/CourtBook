@props(['booking'])
<article class="booking-summary" aria-labelledby="booking-name-{{ $booking->id }}">
    <div class="booking-summary-identity">
        <p class="booking-summary-code">{{ $booking->code }}</p>
        <h2 id="booking-name-{{ $booking->id }}">{{ $booking->court_name }}</h2>
        <div class="booking-summary-status"><x-status :value="$booking->status"/><x-status :value="$booking->payment_status"/></div>
    </div>
    <div class="booking-summary-schedule">
        <p class="booking-summary-date">{{ $booking->starts_at->translatedFormat('l, d M Y') }}</p>
        <p>{{ $booking->starts_at->format('H:i') }}–{{ $booking->ends_at->format('H:i') }} WIB <span> / {{ $booking->duration }} jam</span></p>
        @if($booking->status === 'held')
            <p class="booking-summary-deadline">Bayar sebelum {{ min($booking->hold_expires_at, $booking->balance_due_at)->translatedFormat('d M Y, H:i') }} WIB.</p>
        @elseif($booking->status === 'confirmed' && $booking->payment_status === 'partial')
            <p class="booking-summary-deadline">Lunasi sebelum {{ $booking->balance_due_at->translatedFormat('d M Y, H:i') }} WIB.</p>
        @endif
    </div>
    <div class="booking-summary-total"><span>Total sewa</span><strong>Rp{{ number_format($booking->total, 0, ',', '.') }}</strong></div>
    <a class="booking-summary-detail" href="{{ route('bookings.show', $booking) }}" aria-label="Lihat detail booking {{ $booking->code }}">Lihat detail <x-home-icon name="arrow"/></a>
</article>
