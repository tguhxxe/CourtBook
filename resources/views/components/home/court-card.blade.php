@props(['court'])
<article class="home-court">
    <div class="home-court-visual">
        <a class="home-court-image-link" href="{{ route('courts.show', $court) }}" aria-label="Lihat jadwal {{ $court->name }}">
            <img class="home-court-image" src="{{ asset('images/home/'.($court->sport === 'soccer' ? 'mini-soccer' : 'blue-tennis-court').'.webp') }}" width="1536" height="1024" alt="Ilustrasi {{ $court->sport === 'soccer' ? 'lapangan mini soccer' : 'lapangan tenis hard court' }}" loading="lazy">
        </a>
        <span class="home-sport-label"><x-home-icon :name="$court->sport === 'soccer' ? 'soccer' : 'tennis'"/>{{ $court->sport === 'soccer' ? 'Mini soccer' : 'Tenis' }}</span>
        <div class="home-court-title">
            <h3><a href="{{ route('courts.show', $court) }}">{{ $court->name }}</a></h3>
            <p>{{ $court->sport === 'soccer' ? 'Rumput sintetis' : 'Hard court' }}</p>
        </div>
    </div>
    <div class="home-court-content">
        @if(count($court->facilities ?? []))
            <ul class="home-facilities" aria-label="Fasilitas {{ $court->name }}">
                @foreach(array_slice($court->facilities, 0, 2) as $facility)<li><x-home-icon name="check"/>{{ $facility }}</li>@endforeach
            </ul>
        @endif
        <div class="home-court-booking">
            <div class="home-court-price"><span>Tarif per jam</span><p><strong>Rp{{ number_format($court->hourly_rate, 0, ',', '.') }}</strong></p></div>
            <a class="home-schedule-link" href="{{ route('courts.show', $court) }}" aria-label="Lihat jadwal {{ $court->name }}"><x-home-icon name="calendar"/>Lihat jadwal</a>
        </div>
    </div>
</article>
