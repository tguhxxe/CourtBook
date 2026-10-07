# Midtrans Sandbox

Implementasi berdasarkan dokumentasi resmi yang dibaca saat implementasi:
- https://docs.midtrans.com/docs/snap-snap-integration-guide
- https://docs.midtrans.com/docs/https-notification-webhooks
- https://docs.midtrans.com/reference/get-transaction-status
- https://docs.midtrans.com/docs/snap-advanced-feature

Semua endpoint hardcoded Sandbox; tidak tersedia mode live. Snap POST app.sandbox.midtrans.com/snap/v1/transactions dari backend HTTP Basic auth server key, body order UUID/gross integer/customer/expiry. Browser hanya menerima Snap token dan Client Key, memuat snap.js sandbox. HTTP 15s/connect 5s; tidak retry POST karena efek samping tidak idempotent.

## Konfigurasi manual
Isi .env MIDTRANS_SERVER_KEY dan MIDTRANS_CLIENT_KEY dari dashboard Sandbox. MIDTRANS_MERCHANT_ID opsional untuk pemeriksaan merchant tambahan. Jangan salin nilai key ke screenshot/dokumentasi/repository. Placeholder .env.example kosong. Jalankan `php artisan config:clear` dengan PHP kompatibel setelah konfigurasi. Key kosong tidak membuat simulasi sukses, hanya pesan pembayaran belum tersedia.

APP_URL=http://127.0.0.1:8000 untuk reset password lokal. Webhook dari Midtrans memerlukan URL internet; loopback tidak dapat dihubungi Midtrans. Pengguna memilih dan mengoperasikan tunnel sendiri. Tidak memasang tunnel, membuka port publik, atau mengekspos aplikasi otomatis. Jika tunnel sudah dipilih, isi MIDTRANS_PUBLIC_URL=https://alamat-publik-pilihan dan set dashboard payment notification URL https://alamat-publik-pilihan/midtrans/notification. MIDTRANS_PUBLIC_URL mengatur Snap finish callback; notification URL tetap harus diatur pada dashboard. Serve document root public saja. Bila perlu reset password melalui tunnel, pengguna dapat mengubah APP_URL secara sadar.

## Verifikasi server
Signature hash SHA512 concatenation exact order_id + status_code + gross_amount + ServerKey dibandingkan hash_equals. Identitas order lokal dan nominal integer (format digit atau .00), currency IDR jika tersedia, merchant jika dikonfigurasi diperiksa. Lalu GET api.sandbox.midtrans.com/v2/{order_id}/status dengan backend key; status terbaru server otoritatif, tidak memakai callback/redirect browser untuk sukses. settlement atau capture fraud accept dianggap sukses. fraud challenge/pending belum konfirmasi. DB transition idempotent berdasarkan credited_at dan unique refund reference. Paid terminal tidak diturunkan pending; success setelah release dicatat untuk refund manual.

## Rekonsiliasi
Tombol Periksa status pada detail booking/admin dan command `php artisan courtbook:reconcile`. Schedule setiap 5 menit. GET 404 atau gangguan jaringan tetap unknown/uncertain; Snap baru memang bisa belum memiliki status. Jangan membuat attempt baru sampai kanal memberikan status final deny/cancel/expire/failure; attempt dengan token pending valid dapat dibuka lagi untuk kind sama. Jika POST Snap gagal ambigu sebelum token diterima, perlu investigasi order melalui dashboard/provider; pembatalan booking tetap mungkin, tetapi pembayaran kanal terlambat masuk penanganan manual.

## Cara uji sungguhan setelah key tersedia
1. Login pelanggan, booking jadwal masa depan, setujui ketentuan, buka Sandbox Snap untuk DP.
2. Gunakan akun/metode simulasi resmi Sandbox, bukan kartu/dana nyata. Periksa dashboard Sandbox dan endpoint notification melalui tunnel yang dipilih.
3. Pastikan booking confirmed partial sesudah server verify; ulangi delivery notification, saldo tetap sekali.
4. Lakukan balance sebelum deadline dan full pada booking lain. Uji pending, deny, expire, fraud challenge, dan status rekonsiliasi.
5. Batalkan unpaid lalu kirim success terlambat; slot tidak direbut, refund request dicatat.
6. Dokumentasikan bukti tanpa key/data kartu. Refund diproses manual sesuai dukungan kanal; form progres tidak mengeksekusi refund.

Pengujian otomatis memakai Http::fake + preventStrayRequests; bukan transaksi Midtrans sungguhan. Sandbox sungguhan BELUM diuji karena kredensial belum diberikan. Tidak ada pembayaran live atau refund otomatis yang diklaim.

## Pembaruan status otomatis
Halaman pembayaran dan detail booking dengan attempt belum final memanggil endpoint reconcile terautentikasi melalui POST + CSRF. Callback Snap hanya memicu pemeriksaan, bukan bukti pembayaran. Status final mengarahkan ke detail booking terbaru; DP dan lunas memiliki pesan sukses tersendiri. Pemeriksaan berurutan setiap 5 detik, maksimal 12 kali per siklus, berhenti saat halaman ditinggalkan atau sesi kedaluwarsa, dan menyediakan tombol periksa lagi. Webhook dan scheduler tetap diperlukan ketika pelanggan menutup halaman. Pemeriksaan manual tetap mengambil status provider, termasuk untuk transaksi yang sudah dibayar.
