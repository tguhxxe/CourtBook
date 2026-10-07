# Laporan verifikasi implementasi

Tanggal kerja 7 Oktober 2026 (Asia/Jakarta). Lingkungan Windows, Laravel 12.69.3, PHP portabel 8.4.26, Node 22.20.0, npm 10.9.3, SQLite. Perintah PHP dijalankan melalui runtime portable, bukan PHP global Laragon 8.1.10.

## Bukti otomatis
Hasil akhir PHP: 69 tes bermakna, 226 assertion, tanpa kegagalan (tes scaffold assertTrue(true) telah dihapus). Browser: 15 pemeriksaan, 8 scan axe tanpa violations, 0 error JavaScript. Build Vite dan Pint lulus; npm audit 0 vulnerabilities. Premium strict audit 0 findings.

Hasil akhir machine-readable: [evidence/phpunit.xml](evidence/phpunit.xml) dan [evidence/browser-report.json](evidence/browser-report.json). JUnit mencatat jumlah tes/assertion aktual; browser JSON mencatat pemeriksaan/scans/failures. Tes yang sempat gagal selama implementasi telah diperbaiki dan diuji ulang; artifact akhir menjadi acuan, bukan run awal.

| Area | Tes / bukti | Cakupan |
|---|---|---|
| Auth / ownership | tests/Feature/AuthAndAdminTest.php | register role escalation, invalid input, login/logout, guest/admin/customer, detail/cancel/pay booking lain |
| Reset password | AuthAndAdminTest | broker token benar, single use, token invalid, password hash berubah |
| Booking | BookingRulesTest | durasi 0/1/4/5, 30 hari, +2 jam, operasi 07-23, maintenance overlap, adjacent, beda lapangan |
| Harga | BookingRulesTest | integer total, DP round up, remaining, tariff/DP snapshot |
| Lifecycle | BookingRulesTest | exact 15 min expire/release, unpaid cancel, paid cancel exact 24h/23h, balance due, forfeiture DP |
| Pembayaran | PaymentsTest | initial DP/full/balance, signature/amount/order/merchant/currency, duplicate, stale/out-of-order, fraud challenge, late/refund, uncertain, same active attempt, excess credit, provider identity uniqueness |
| SQLite contention | ConcurrencyTest | dua PHP proses terpisah pada database FILE uji; tepat satu BOOKED, satu CONFLICT; satu booking dan dua slot |
| White-box unit | BookingCancellationTest | lima cabang status/dana/batas waktu, remaining |
| Token drift | DesignTokenTest | 9 token palette DESIGN.md sesuai CSS canonical |
| Browser produk | scripts/browser-check.mjs | desktop 1440x1000, narrow 390x844, home/catalog/no-results/clear, validation, password toggle, slot/harga, checkout tanpa key, cancel dialog keyboard/focus, unsaved changes, admin CRUD/maintenance |
| Aksesibilitas terbatas | browser-report.json | axe WCAG tags 2A/AA/2.1AA/2.2AA pada home/login-error/schedule/checkout/admin desktop dan mobile; keyboard skip/native select/dialog, reduced-motion, scrollbar |

Command akhir:
```powershell
./scripts/php.ps1 artisan test --compact --log-junit docs/evidence/phpunit.xml
./scripts/php.ps1 vendor/bin/pint --test
npm run build
npm audit
./scripts/php.ps1 C:/ProgramData/ComposerSetup/bin/composer.phar audit
node scripts/browser-check.mjs
python <skill-dir>/scripts/audit_project.py . --mode strict
npx -p @google/design.md designmd lint DESIGN.md
```

Tidak ada TypeScript; typecheck tidak berlaku pada Blade/PHP/vanilla JS. PHP formatter Pint, suite PHP, Vite build dan browser runtime dipakai. Static premium audit hanya membaca ekstensi sumber yang didukung auditor; Blade diuji lewat HTTP rendering/browser. Lint DESIGN.md memiliki 0 error, 6 warning orphaned-token karena component config visual ada di prose/CSS, bukan referensi token frontmatter. Palette drift diuji secara eksplisit.

## Visual QA
Screenshot [home desktop](evidence/home-desktop.png), [checkout desktop](evidence/checkout-desktop.png), [jadwal mobile](evidence/schedule-mobile.png), [admin mobile](evidence/admin-mobile.png). Screenshot desktop dan dua tampilan mobile sudah diperiksa secara visual. Overflow tabel admin mobile ditemukan dan diperbaiki dengan min-width:0 pada owner konten; tabel tetap memiliki scroll horizontal, document tidak overflow. Teks simbol yang rusak encoding PowerShell diganti entities/ascii. Ilustrasi SVG orisinal, bukan klaim foto venue.

Script browser membuat beberapa booking simulasi yang dibatalkan dan lapangan/maintenance sementara yang dihapus melalui UI. Riwayat pembatalan demo tetap ada sebagai bukti; tidak ada transaksi sukses palsu. Script dibatasi base URL localhost, tidak untuk produksi.

## Belum diuji / batas bukti
Midtrans Sandbox sungguhan BELUM diuji, tanpa kredensial. Tidak ada pembayaran live, refund otomatis, webhook tunnel publik, atau kirim email SMTP nyata. Http::fake dengan preventStrayRequests dipakai untuk Midtrans; password broker diuji Notification fake, mail log lokal disediakan untuk demo. Load/stress/performance kuantitatif, penetrasi keamanan, browser/device fisik lain, screen reader/manual full WCAG, zoom 200% matrix dan ketahanan jaringan semua flow belum selesai. Scans axe bersih bukan sertifikasi WCAG. Tidak ada persetujuan asisten atau klaim UNQ-1 selesai.

Pemeriksaan tambahan: 52 file Blade terkompilasi lulus PHP syntax lint. Audit Composer saat update lockfile awal melaporkan tidak ada advisory; percobaan audit penutup `composer audit --locked` tidak selesai karena endpoint Packagist timeout, termasuk saat dicoba ulang. Karena itu freshness audit Composer terakhir belum diverifikasi; jalankan ulang saat jaringan tersedia. Npm audit penutup berhasil dengan 0 vulnerabilities.
