@props(['settings'])

<section class="home-how" id="cara-booking" aria-labelledby="home-how-title">
    <div class="home-wrap">
        <div class="home-how-heading">
            <h2 id="home-how-title">Atur jadwal.<br>Siapkan permainanmu.</h2>
            <div class="home-how-intro">
                <p>Pertama kali booking? Ikuti tiga langkah ini untuk memesan lapangan dan bersiap main bersama tim.</p>
                <a class="home-button home-button-light" href="{{ route('courts.index') }}">Cari lapangan <x-home-icon name="arrow"/></a>
            </div>
        </div>
        <ol class="home-booking-steps" role="list">
            <li>
                <div class="home-step-rail" aria-hidden="true"><span class="home-step-number">1</span><x-home-icon name="calendar"/></div>
                <h3>Pilih lapangan<br>dan jadwal.</h3>
                <p>Cek jam yang tersedia, lalu tentukan tanggal dan durasi bermain di lapangan pilihanmu.</p>
                <span class="home-step-detail">Lihat jadwal tanpa perlu masuk</span>
            </li>
            <li>
                <div class="home-step-rail" aria-hidden="true"><span class="home-step-number">2</span><x-home-icon name="wallet"/></div>
                <h3>Periksa pesanan.<br>Lanjutkan pembayaran.</h3>
                <p>Masuk atau buat akun untuk memesan. Bayar DP {{ $settings->dp_percent }}% atau lunas dalam {{ config('courtbook.hold_minutes') }} menit.</p>
                <span class="home-step-detail">Tunggu pembayaran terverifikasi</span>
            </li>
            <li>
                <div class="home-step-rail" aria-hidden="true"><span class="home-step-number">3</span><x-home-icon name="tennis"/></div>
                <h3>Pastikan konfirmasi.<br>Waktunya main!</h3>
                <p>Pastikan booking sudah terkonfirmasi. Jika memilih DP, lunasi sisanya paling lambat {{ config('courtbook.lead_hours') }} jam sebelum jadwal.</p>
                <span class="home-step-detail">Siapkan perlengkapan dan timmu</span>
            </li>
        </ol>
    </div>
</section>
