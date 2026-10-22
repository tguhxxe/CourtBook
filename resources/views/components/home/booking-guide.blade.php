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
                <span class="home-step-number" aria-hidden="true">1</span>
                <div>
                    <h3>Pilih jadwal.</h3>
                    <p>Lihat lapangan tanpa perlu masuk. Pilih tanggal, jam yang tersedia, dan durasi bermain.</p>
                </div>
            </li>
            <li>
                <span class="home-step-number" aria-hidden="true">2</span>
                <div>
                    <h3>Pesan dan bayar.</h3>
                    <p>Masuk atau buat akun. Periksa pesanan, bayar DP {{ $settings->dp_percent }}% atau lunas dalam {{ config('courtbook.hold_minutes') }} menit, lalu tunggu pembayaran terverifikasi.</p>
                </div>
            </li>
            <li>
                <span class="home-step-number" aria-hidden="true">3</span>
                <div>
                    <h3>Bersiap main.</h3>
                    <p>Pastikan booking terkonfirmasi. Jika memilih DP, lunasi sisanya paling lambat {{ config('courtbook.lead_hours') }} jam sebelum jadwal.</p>
                </div>
            </li>
        </ol>
    </div>
</section>
