<section class="home-wrap home-payments" aria-labelledby="home-payments-title">
    <h2 id="home-payments-title">Metode pembayaran</h2>
    <p>Metode yang tersedia ditampilkan saat pembayaran.</p>
    <ul class="home-payment-logos" aria-label="Bank dan layanan pembayaran">
        @foreach(['bca' => 'BCA', 'bri' => 'BRI (BRIVA)', 'mandiri' => 'Mandiri', 'bni' => 'BNI', 'bsi' => 'BSI', 'gopay' => 'GoPay', 'dana' => 'DANA', 'qris' => 'QRIS'] as $logo => $name)
            <li><img src="{{ asset('images/payments/'.$logo.'.png') }}" alt="{{ $name }}" width="100" height="32" loading="lazy"></li>
        @endforeach
    </ul>
</section>
