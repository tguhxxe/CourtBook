# Kebutuhan CourtBook

Praktikan Teguh Setia, NIM 202310370311061, Kelas B. Asisten Modul 1: Alip. Repository tujuan: https://github.com/tguhxxe/CourtBook.git. Tema belum disetujui; deadline belum diketahui. Fokus pengujian satu role Pelanggan sampai UAP. Admin pendukung operasional. Implementasi lokal, seluruh venue/fasilitas/demo simulasi. Tidak ada Test Plan final atau perubahan spreadsheet registrasi dalam pekerjaan ini.

| ID | Kebutuhan fungsional | Implementasi / bukti utama |
|---|---|---|
| FR-AUTH-01 | Register customer, login/logout, role/policy akses | AuthController, AuthAndAdminTest |
| FR-AUTH-02 | Forgot/reset password token sah sekali pakai; mail log lokal | Password broker, AuthAndAdminTest |
| FR-COURT-01 | Cari nama dan filter olahraga | CourtController, AuthAndAdminTest |
| FR-COURT-02 | Detail fasilitas/tarif, jadwal tersedia/terisi/maintenance | CourtController, Reservation, browser |
| FR-BOOK-01 | Buat booking, hitung integer rupiah dan snapshot | BookingService, BookingRulesTest |
| FR-BOOK-02 | Riwayat/detail/status hanya milik pelanggan | BookingPolicy, AuthAndAdminTest |
| FR-BOOK-03 | Pembatalan sesuai batas dan refund manual | BookingService, BookingRulesTest |
| FR-PAY-01 | DP/lunas Sandbox Snap bila key tersedia | PaymentService, PaymentsTest mock |
| FR-PAY-02 | Pelunasan sisa sebelum deadline | PaymentService, PaymentsTest |
| FR-PAY-03 | Signature, GET status, identitas/nominal, idempotensi | PaymentService, PaymentsTest |
| FR-PAY-04 | Rekonsiliasi tombol dan terjadwal | PaymentController, ReconcilePayments |
| FR-PROFILE-01 | Profil nama/email/telepon dan password terverifikasi | ProfileRequest, AuthAndAdminTest |
| FR-ADM-01 | Dashboard, CRUD lapangan/fasilitas/tarif/aktif | Admin controllers, AuthAndAdminTest |
| FR-ADM-02 | Tambah/hapus maintenance tanpa overlap | BookingService, AuthAndAdminTest |
| FR-ADM-03 | Baca booking/transaksi, catat progres refund | Admin controllers, AuthAndAdminTest |
| FR-ADM-04 | Jam operasional/DP terkontrol, snapshot tetap | OperationsController, AuthAndAdminTest |
| FR-JOB-01 | Expire hold dan DP belum dilunasi, release slot | ExpireBookings, BookingRulesTest |

| ID | Kebutuhan nonfungsional / penerimaan awal |
|---|---|
| NFR-01 | Laravel MVC/service/request/policy, SQLite lokal, tidak push/deploy |
| NFR-02 | Serialize writer sebelum domain read + unique slot, uji contention dua proses |
| NFR-03 | Password hashed, CSRF kecuali webhook signed, throttle auth/payment, role ownership |
| NFR-04 | Server key hanya environment backend; callback browser tidak otoritatif |
| NFR-05 | Bahasa Indonesia, WIB, responsive desktop/mobile; akses keyboard, label/focus |
| NFR-06 | Mock transaksi untuk automated regression, bukti hasil dapat ditelusuri |
| NFR-07 | Build lokal dan docs instalasi/scheduler/mail log/tunnel manual |
| NFR-08 | Latensi/beban, audit keamanan menyeluruh, usability dan aksesibilitas lintas device menjadi pengujian lanjutan; belum diklaim selesai |

Data awal: Tennis Court A/B Rp100.000 per jam; Mini Soccer Arena Rp300.000 per jam. Jam 07.00-23.00 WIB setiap hari, tarif sama setiap hari. Alamat contoh Jl. Olahraga Contoh No. 10, Malang (simulasi). Batas/ketentuan rinci di business-rules.md. Identitas lapangan disimpan pada booking sehingga penggantian nama tidak mengubah riwayat.


FR-REFUND-01: Pelanggan dapat mengajukan refund pada booking berbayar yang memenuhi batas pembatalan 24 jam dengan alasan, dan memantau nominal, status, serta catatan admin melalui Refund saya. Pengajuan membatalkan booking dan memakai proses refund admin yang ada. Refund terlambat otomatis juga ditampilkan; transfer tetap manual.
