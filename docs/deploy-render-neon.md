# Onlinekan CourtBook: Render Free + Neon Free

Konfigurasi disiapkan untuk demo praktikum, PHP 8.4, Apache dan PostgreSQL. SQLite/.env/data lokal tidak dimasukkan ke image. Database online baru tidak otomatis berisi akun, booking atau pembayaran localhost.

## 1. Buat akun
- Render: https://dashboard.render.com/ (masuk dengan GitHub).
- Neon: https://console.neon.tech/ (masuk dengan GitHub).
- Di Neon, buat project CourtBook menggunakan plan Free. Pilih region terdekat yang tersedia, lalu buka Connect. Pilih koneksi direct/non-pooled untuk konfigurasi awal; salin connection string PostgreSQL dengan sslmode=require. Simpan hanya di Environment Render sebagai DB_URL.

## 2. Siapkan GitHub
Render mengambil source dari repository GitHub, bukan folder laptop. Pastikan perubahan aplikasi dan file deployment terbaru sudah di-commit dan di-push ke https://github.com/tguhxxe/CourtBook. Periksa daftar file sebelum commit: jangan sertakan .env, database.sqlite, vendor, node_modules, log, dokumen sementara atau secret.

## 3. Buat layanan Render
1. Di Render pilih New > Blueprint, hubungkan repository CourtBook, dan pilih branch yang berisi render.yaml terbaru.
2. Render akan membaca render.yaml. Pastikan service CourtBook memakai plan Free dan Docker. Tidak perlu membuat database Render; database memakai Neon.
3. Isi variable yang diminta:

| Variable | Isi |
|---|---|
| APP_KEY | Kunci baru untuk lingkungan online, lihat perintah berikut |
| DB_URL | Connection string Neon, jangan sertakan perintah psql atau tanda kutip pembungkus |
| INITIAL_ADMIN_EMAIL | Email admin online yang Anda pilih |
| INITIAL_ADMIN_PASSWORD | Password kuat minimal 12 karakter, berbeda dari akun demo lokal |

Buat APP_KEY baru di terminal lokal:

```powershell
./scripts/php.ps1 artisan key:generate --show
```

Perintah ini menampilkan kunci tanpa mengubah .env. Salin hasil base64:... ke Environment Render. Jangan bagikan atau commit hasilnya. Pertahankan kunci yang sama saat redeploy agar session dan data terenkripsi tetap konsisten.

4. Jalankan pembuatan Blueprint/deploy. Aplikasi menjalankan migrate (tanpa menghapus data), seed idempotent, membuat admin awal jika belum ada admin, kemudian menyalakan Apache dan scheduler. Tidak menggunakan migrate:fresh.
5. Buka URL https://courtbook-....onrender.com yang diberikan Render. APP_URL otomatis memakai RENDER_EXTERNAL_URL. Untuk custom domain, isi APP_URL dengan URL HTTPS domain tersebut.
6. Login admin dengan kredensial tadi. Akun demo CourtBook123! tidak dibuat di production. Untuk pelanggan, gunakan halaman Daftar dengan alamat email Anda.
7. Sesudah admin terbentuk, hapus INITIAL_ADMIN_PASSWORD dari Environment Render. Restart tidak memerlukan password bootstrap lagi selama akun admin masih ada.

## 4. Aktifkan Midtrans Sandbox
Pada Environment Render tambahkan MIDTRANS_SERVER_KEY, MIDTRANS_CLIENT_KEY dan MIDTRANS_MERCHANT_ID milik akun Sandbox Anda. MIDTRANS_PUBLIC_URL otomatis mengikuti APP_URL kecuali Anda mengisinya sendiri. Simpan perubahan/redeploy.

Pada dashboard Midtrans Sandbox atur payment notification URL menjadi:

```text
https://URL-WEBSITE-ANDA/midtrans/notification
```

Uji booking baru, pembayaran VA Sandbox, callback/webhook, DP/pelunasan, dan pengajuan refund. Transaksi localhost tidak dipindahkan ke database online. Refund tetap proses manual admin.

## 5. Email forgot password
Default MAIL_MAILER=log: tautan reset dicatat di log Render, belum dikirim ke inbox. Untuk mengirim nyata gunakan layanan email HTTP API (misalnya mailer Resend yang sudah ada pada config/mail.php; perlu memasang transport/provider sesuai dokumentasi Laravel sebelum diaktifkan). Render Free memblokir port SMTP umum 25/465/587. Konfigurasi layanan email merupakan langkah terpisah; jangan menganggap hosting membuat email otomatis aktif.

## 6. Verifikasi sebelum demo
- /up dan halaman utama dapat dibuka melalui HTTPS.
- Register/login/logout pelanggan dan admin berjalan tanpa error 419.
- Dua pelanggan tidak bisa mengambil slot yang sama; lakukan uji PostgreSQL, bukti tes SQLite tidak menggantikannya.
- Payment notification dan polling mengubah status setelah verifikasi Midtrans.
- Pelanggan tidak bisa membaca booking/refund pengguna lain.
- Data akun/booking tetap ada setelah redeploy.
- Booking held kedaluwarsa dan slot dilepas. Supervisor menjalankan schedule:work selama service aktif.

Render Free tidur setelah sekitar 15 menit tidak dikunjungi; waktu buka pertama bisa sekitar satu menit. Scheduler ikut berhenti saat service tidur. Lazy expiry tetap memeriksa batas saat pengguna membuka/membuat booking; layanan gratis ini bukan jaminan scheduler aktif 24 jam. Webhook yang datang ketika service tidur perlu diuji, termasuk retry/polling. Tanpa metode pembayaran, Render dapat menangguhkan service jika kuota gratis habis.

## Status verifikasi
Konfigurasi dibuat di repository. Docker belum dibangun lokal karena Docker tidak terpasang. Koneksi/migrasi/concurrency PostgreSQL dan deploy Render belum diuji karena akun/kredensial hosting belum tersedia. Suite lokal, build Vite dan tes bootstrap admin dipakai untuk verifikasi awal; keberhasilan online harus diperiksa setelah deploy.

## Referensi resmi
- https://render.com/docs/deploy-php-laravel-docker
- https://render.com/docs/blueprint-spec
- https://render.com/docs/free
- https://neon.com/docs/connect/connect-from-any-app
- https://laravel.com/docs/12.x/requests#configuring-trusted-proxies
