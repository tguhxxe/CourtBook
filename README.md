# CourtBook

Aplikasi lokal praktikum PKPL/SQA untuk booking tenis dan mini soccer. Teguh Setia - 202310370311061 - Kelas B; asisten Modul 1 Alip. Tema belum disetujui, deadline belum diketahui. Fokus uji role Pelanggan sampai UAP; admin mendukung operasional. Venue, alamat/fasilitas, akun demo dan ilustrasi adalah simulasi.

## Lingkungan
Laravel 12; PHP 8.4+ (lock dependency saat ini memakai komponen Symfony 8 yang memerlukan PHP 8.4); Composer 2; Node 22.20+/npm; SQLite pdo_sqlite. PHP global Laragon 8.1 tidak dipakai. Pada komputer ini PHP portabel berada di `%LOCALAPPDATA%/CourtBook/php84` dan wrapper `scripts/php.ps1` memilihnya tanpa mengubah PATH/global. Runtime portabel tidak disimpan di repository. Komputer lain gunakan PHP 8.4 dengan curl/fileinfo/mbstring/openssl/pdo_sqlite/sqlite3/zip. Unduh PHP Windows resmi: https://www.php.net/downloads.php?os=windows .

## Instalasi baru
Dari root CourtBook, gunakan PHP 8.4 pada PATH atau ganti setiap `php` dengan `./scripts/php.ps1` di PowerShell komputer ini:

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
New-Item database/database.sqlite -ItemType File -ErrorAction SilentlyContinue
php artisan migrate --seed
npm ci
npm run build
php artisan serve --host=127.0.0.1 --port=8000
```

Pada komputer ini Composer default memakai PHP 8.1. Gunakan PHP portabel secara eksplisit untuk Composer:

```powershell
./scripts/php.ps1 C:/ProgramData/ComposerSetup/bin/composer.phar install
./scripts/php.ps1 artisan serve --host=127.0.0.1 --port=8000
```

Set APP_URL=http://127.0.0.1:8000, APP_LOCALE=id, DB_CONNECTION=sqlite, MAIL_MAILER=log. `.env` ter-ignore; jangan commit key. Seed idempotent tidak mereset perubahan lapangan/pengaturan/password. Aplikasi yang sudah disiapkan cukup menjalankan command serve. Buka http://127.0.0.1:8000 .

## Akun simulasi lokal
Pelanggan `pelanggan@courtbook.test`, admin `admin@courtbook.test`, kata sandi keduanya `CourtBook123!`. Seeder hanya membuat akun demo pada local/testing. Register membuat pelanggan; tidak ada UI menaikkan role. Admin di /admin. Tidak ada booking/pembayaran palsu di seed.

## Aset dan scheduler
`npm run build` menyediakan build Vite. Untuk development, terminal lain `npm run dev`. Aplikasi memakai ilustrasi SVG orisinal dan font sistem, tanpa foto tak berlisensi.

Jalankan terminal kedua selama demo:

```powershell
./scripts/php.ps1 artisan schedule:work
```

Scheduler expire tiap menit dan reconcile tiap lima menit. Command manual `courtbook:expire` dan `courtbook:reconcile`. Lazy expire juga dijalankan sebelum pemeriksaan booking/availability, tetapi scheduler tetap diperlukan untuk release proaktif tanpa kunjungan web. Tidak perlu worker queue terpisah.

## Forgot password demo lokal
Password broker Laravel memakai token ter-hash, expiry/throttle standar, sekali pakai. MAIL_MAILER=log tidak mengirim email nyata. Setelah Minta tautan reset, baca log lokal sendiri:

```powershell
Get-Content storage/logs/laravel.log -Tail 120
```

Cari URL `/reset-password/` pada email log dan buka URL lengkap (token/email). Jangan membagikan log karena berisi tautan reset sensitif. APP_URL harus sesuai server lokal. Pengujian otomatis memakai Notification fake, memeriksa reset sekali pakai dan token invalid.

## Midtrans
Isi key Sandbox di .env lalu config:clear. Tanpa key, booking dapat dibuat tetapi tidak dikonfirmasi dan akan expired. Integrasi server/Snap/webhook/reconcile ada; Sandbox sungguhan belum diuji. Lihat docs/midtrans-sandbox.md untuk endpoint publik via tunnel pilihan pengguna dan pengujian manual. Tidak ada tunnel terpasang otomatis, pembayaran live atau refund otomatis.

## Pengujian
```powershell
./scripts/php.ps1 artisan test
./scripts/php.ps1 vendor/bin/pint --test
npm run build
npm audit
```

Tes memakai SQLite terisolasi in-memory; concurrency memakai database uji file terpisah. Http Midtrans di-mock dan stray requests dilarang. Browser E2E lihat docs/testing-report.md dan scripts/browser-check.mjs; jalankan setelah serve lokal. Jangan arahkan script browser ke aplikasi produksi karena script membuat data demo.

## Dokumentasi dan status
- docs/requirements.md: ID kebutuhan dan penerimaan awal.
- docs/business-rules.md: aturan/delimitasi waktu dan state.
- docs/architecture.md: model, concurrency, retensi.
- docs/midtrans-sandbox.md: konfigurasi/verifikasi dan uji manual.
- docs/practicum-readiness.md: kesiapan dengan batas bukti.
- docs/testing-report.md: pengujian aktual dan keterbatasan.
- DESIGN.md dan UX-CONTRACT.md: visual, canonical UI dan perilaku.

Tidak ada push, deploy, perubahan remote, Test Plan final, spreadsheet registrasi, atau persetujuan asisten dalam pekerjaan ini. Audit beban, penetrasi, perangkat nyata/assistive technology lengkap, dan transaksi Sandbox sungguhan adalah tindak lanjut.
