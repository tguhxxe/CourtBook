# Kesiapan praktikum PKPL/SQA

Teguh Setia - 202310370311061 - Kelas B - Asisten Modul 1 Alip. Tema CourtBook belum disetujui. Deadline belum diketahui. Pengujian praktikum fokus Pelanggan secara konsisten sampai UAP; admin hanya pendukung. Tidak ada Test Plan final atau perubahan spreadsheet registrasi.

**Batas pemetaan:** pengguna memberikan daftar ID, tetapi definisi rubrik asli, PDF modul dan template tidak dilampirkan. Kolom cakupan di bawah adalah pemetaan kerja sementara dari spesifikasi pengguna, BUKAN kutipan definisi resmi tiap ID. Asisten perlu memeriksa arti dan keterkaitannya saat dokumen modul tersedia. Karena itu tidak ada klaim checklist seluruhnya TRUE atau kelulusan modul.

| ID | Cakupan kerja sementara | Sudah diimplementasikan | Sudah diverifikasi | Belum selesai / persetujuan |
|---|---|---|---|---|
| PRJ-1 | Identitas/proyek | CourtBook, Laravel, identitas praktikan terdokumentasi | Build, halaman lokal, seed | Definisi rubrik perlu dicocokkan |
| PRJ-2 | Tema dan kelayakan | Tenis + mini soccer satu kompleks simulasi | Alur pelanggan diimplementasikan dan diuji | Persetujuan tema oleh Alip BELUM ada |
| PRJ-3 | Repository/dokumentasi | Source, lockfiles, README dan docs lokal | Struktur proyek dan Git diperiksa | Remote belum diubah/push; validasi rubrik repository oleh asisten |
| ROL-1 | Satu role fokus | Pelanggan fokus, admin operasional | Policy ownership, guest/customer/admin tests | Jaga role fokus sampai UAP; persetujuan pencatatan role asisten |
| FTR-1 | Input angka/teks dan validasi | Register/profil/search, jadwal, durasi, tarif/DP | AuthAndAdminTest, BookingRulesTest | Cocokkan kriteria rubrik asli |
| FTR-2 | CRUD dan perubahan status | CRUD lapangan/maintenance, booking cancellation, progres refund | HTTP tests dan browser create/edit/delete | Refund kanal manual, belum transfer nyata |
| FTR-3 | Kondisi/aturan bisnis | Deadline, maintenance, overlap, pembayaran terverifikasi | Boundary/state/payment mocks | Integrasi Sandbox sungguhan belum diuji |
| UNQ-1 | Keunikan tema | Aturan DP/pelunasan lintas dua olahraga | Tidak ada klaim keunikan kelas | WAJIB verifikasi asisten: ada proyek booking olahraga lain pada daftar kelas |
| RDY-3 | Aplikasi lokal | SQLite, portable PHP, Laravel, Vite | Migrasi/seed, test/build, localhost browser | PHP portabel diperlukan karena PHP global 8.1 |
| RDY-4 | Pengujian otomatis | Unit, feature, dua proses SQLite, browser | Bukti phpunit.xml dan browser-report.json | Bukan klaim coverage 100%; teknik final cocokkan modul/template |
| RDY-5 | Integrasi/keamanan pembayaran | Snap Sandbox, webhook signature/GET status/reconcile | Mock valid/invalid/duplikat/terlambat/out-of-order | Key Sandbox dan tunnel pilihan pengguna; live tidak dibuat |
| RDY-6 | Nonfungsional/evidence | Responsive/keyboard/axe, UI contract, evidence | Chromium desktop/mobile dan axe scoped | Beban/performance, penetration, real device/AT lengkap belum dilakukan |
| REQ-1 | Kebutuhan terlacak | ID FR/NFR/BR, traceability docs | Test mappings dan bukti aktual | Review/approval kebutuhan dan definisi rubrik oleh asisten |

## Status keseluruhan
- **Implementasi selesai:** aplikasi lokal pelanggan/admin, backend booking/payment/refund, docs, automated tests dan UI responsif.
- **Verifikasi teknis selesai:** hasil command aktual terdapat di testing-report.md, phpunit.xml, browser-report.json dan premium-audit.json.
- **Belum diverifikasi eksternal:** kredensial/transaksi Midtrans Sandbox sungguhan, endpoint tunnel/webhook internet, eksekusi refund kanal.
- **Perlu persetujuan asisten:** tema, UNQ-1, aturan demo (DP hangus/deadline/refund), kesesuaian ID rubrik, teknik pengujian dan scope modul selanjutnya.

Jika modul PDF/template kemudian diberikan, baca sebagai referensi tugas, ikuti struktur template dan catat perbedaan petunjuk teknik pengujian/detail modul berikutnya. Jangan mengarang keputusan asisten. Dokumen ini adalah catatan kesiapan, bukan Test Plan final.
