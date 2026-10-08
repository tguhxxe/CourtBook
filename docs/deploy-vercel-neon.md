# CourtBook di Vercel Hobby + Neon

Konfigurasi menggunakan runtime komunitas vercel-php@0.8.0 (PHP 8.4), Node 22, PostgreSQL Neon. Ini deployment demo praktikum. Koneksi PostgreSQL, build runtime Vercel dan webhook publik masih harus diverifikasi di hosting.

## Pada halaman New Project Vercel
- Team: personal / Hobby.
- Project Name: court-book (atau nama tersedia).
- Root Directory: ./.
- Application Preset: Other.
- Build Command: npm run build:vercel.
- Output Directory: vercel-public.
- Install Command: npm ci.
- Node.js Version: 22.x (Project Settings jika tidak muncul saat import).

Repository main harus sudah berisi api/index.php, vercel.json, scripts/vercel-assets.mjs dan scripts/vercel-prepare.php terbaru sebelum Deploy. Runtime PHP memasang Composer dependencies; build npm mempublikasikan hanya aset yang disetujui. PHP source, .env dan database SQLite tidak menjadi file publik.

## Environment Variables
Isi untuk Production; gunakan database lain jika mengaktifkan Preview. Jangan menempelkan isi .env localhost sekaligus.

| Key | Value |
|---|---|
| APP_ENV | production |
| APP_DEBUG | false |
| APP_KEY | kunci baru base64:... |
| APP_URL | URL HTTPS production project Anda, misalnya https://court-book.vercel.app jika domain itu diberikan Vercel |
| APP_LOCALE | id |
| APP_FALLBACK_LOCALE | id |
| DB_CONNECTION | pgsql |
| DB_URL | connection string Neon yang baru setelah password yang pernah dibagikan di chat di-reset |
| DB_SSLMODE | require |
| SESSION_DRIVER | database |
| SESSION_SECURE_COOKIE | true |
| CACHE_STORE | database |
| QUEUE_CONNECTION | sync |
| LOG_CHANNEL | stderr |
| MAIL_MAILER | log |
| TRUST_PROXIES | * |
| INITIAL_ADMIN_EMAIL | email admin online pilihan Anda |
| INITIAL_ADMIN_PASSWORD | password admin minimal 12 karakter |

Buat kunci online baru tanpa mengubah .env lokal:

```powershell
./scripts/php.ps1 artisan key:generate --show
```

Salin hasilnya langsung ke APP_KEY Vercel, jangan ke chat atau GitHub. Pertahankan APP_KEY saat redeploy. Akun demo CourtBook123! tidak dibuat di production; pelanggan mendaftar melalui website.

Saat build production, script menjalankan migrate --force, seed idempotent, dan bootstrap admin. Tidak menjalankan migrate:fresh. Build Preview melewati setup database. Password admin tidak diubah ketika admin sudah ada. Setelah admin berhasil dibuat, INITIAL_ADMIN_PASSWORD dapat dihapus.

## Midtrans
Tambahkan MIDTRANS_SERVER_KEY, MIDTRANS_CLIENT_KEY dan MIDTRANS_MERCHANT_ID Sandbox ke Environment Vercel. MIDTRANS_PUBLIC_URL diisi sama dengan APP_URL. Pada dashboard Midtrans Sandbox, notification URL adalah https://DOMAIN-ANDA/midtrans/notification. Setelah mengubah environment, redeploy.

Uji pembayaran baru pada database online; transaksi localhost tidak otomatis dipindahkan. Pastikan deployment protection tidak memblokir notifikasi Midtrans ke domain production. Status berhasil tetap berasal dari verifikasi backend.

## Batas serverless
- Storage sementara hanya untuk compiled view/cache lokal sementara; akun, session dan booking disimpan di PostgreSQL.
- schedule:work tidak dapat berjalan terus-menerus di Vercel. Paket Hobby hanya mendukung cron sekali sehari, sehingga scheduler menit untuk expire/reconcile belum tersedia.
- Lazy expiry tetap bekerja saat booking dibaca/dibuat; polling halaman dan webhook melakukan verifikasi pembayaran. Rekonsiliasi proaktif ketika semua halaman ditutup membutuhkan scheduler eksternal yang terautentikasi, belum dikonfigurasi.
- MAIL_MAILER=log belum mengirim email nyata. Tautan reset berada di log function yang hanya dapat diakses pengelola; aktifkan provider email sebagai pekerjaan terpisah.
- Refund pelanggan tetap ditangani admin secara manual.

## Pemeriksaan setelah deploy
1. /up, halaman utama, register/login dan admin terbuka tanpa error.
2. Pastikan APP_URL sama dengan domain production yang diberikan Vercel.
3. Booking disimpan, tidak overlap, dan data bertahan setelah redeploy.
4. Verifikasi VA Sandbox, status sukses, webhook, DP/pelunasan serta refund pelanggan.
5. Verifikasi isolasi data antar pengguna dan kedaluwarsa booking.

## Referensi
- https://github.com/vercel-community/php
- https://vercel.com/docs/plans/hobby
- https://vercel.com/docs/cron-jobs/usage-and-pricing
