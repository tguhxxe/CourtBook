# Aturan bisnis demo

Sumber: instruksi pengguna, belum persetujuan asisten. Business timezone Asia/Jakarta; waktu disimpan lokal WIB konsisten pada koneksi/app. Aturan versi awal dapat direvisi setelah diskusi, dengan regresi diperbarui.

| ID | Aturan dan batas |
|---|---|
| BR-01 | Slot jam bulat 1 jam; durasi integer 1-4 jam; backend memeriksa semua durasi |
| BR-02 | Tanggal maksimal hari WIB +30 (inklusif); mulai minimal now +2 jam (inklusif) |
| BR-03 | Seluruh durasi di jam operasional; tidak melewati tengah malam; default 07-23 |
| BR-04 | Unique court/start per jam; rentang [mulai, selesai), booking berurutan boleh, maintenance memakai constraint yang sama |
| BR-05 | Tarif, nama, DP%, total, DP amount integer snapshot; DP dibulatkan ke atas integer rupiah |
| BR-06 | Held 15 menit; tepat batas menjadi expired. Slot dilepas; scheduler tiap menit dan expire lazy saat baca/buat/mutasi booking |
| BR-07 | DP 50% default admin 1-100 untuk booking baru; pelanggan boleh bayar full; confirmed hanya dari pembayaran terverifikasi server |
| BR-08 | Balance due starts_at -2 jam snapshot. Confirmed partial yang belum lunas tepat batas dibatalkan; DP hangus, tidak refund otomatis |
| BR-09 | Satu active attempt per booking; order UUID tiap percobaan; uncertain tidak dilepas hanya karena timeout. Saldo tidak boleh dikredit melebihi total |
| BR-10 | Unpaid boleh cancel; paid cancel hanya now <= starts_at -24 jam. Slot dilepas, dana yang dikredit pada booking diajukan refund manual |
| BR-11 | Expired/cancelled tidak pernah confirmed lagi; sukses terlambat/overpay dicatat paid pada attempt dan refund request, tanpa kredit ke booking atau mengambil slot kembali |
| BR-12 | Refund requested -> reviewing -> processed/rejected; admin mencatat bukti/progres; processed bukan eksekusi transfer dan tidak mengubah status payment |
| BR-13 | Tidak ada reschedule. Nonaktif lapangan mencegah booking baru, tidak membatalkan booking lama |
| BR-14 | Maintenance masa depan, jam bulat, <=168 jam, tanpa overlap. Pengurangan jam operasional ditolak jika memotong booking aktif |

Konfigurasi: setting DB jam buka/tutup dan DP%, config/courtbook.php hold/advance/lead/cancel/max_duration. Durasi UI mengikuti kontrak 1-4; jika kebijakan berubah, sinkronkan Form Request, UI, docs dan tes. Balance/hold/DP snapshots disimpan; perubahan configuration lead/cancel memerlukan analisis migrasi aturan lama. Batas cancellation saat ini mengikuti config aktif, bukan snapshot.

Status booking held/confirmed/cancelled/expired; status pembayaran booking unpaid/partial/paid (jumlah dialokasikan), attempt creating/uncertain/pending/paid/deny/cancel/expire/failure. Refund memiliki lifecycle terpisah. GET status current dipakai menghadapi notification stale; pending tidak menurunkan terminal, paid tidak dikredit dua kali. Refund/chargeback provider dicatat untuk pemeriksaan admin, tidak otomatis mengurangi saldo booking karena perlu bukti jumlah dana aktual dari kanal.

Transaksi close-time berada pada same date. Hold tetap 15 menit, tetapi semua pembayaran harus terverifikasi sebelum batas mulai -2 jam. Booking pada tepat batas minimal +2 jam memenuhi validasi jadwal tetapi tidak memiliki waktu pembayaran tersisa; UI menampilkan batas ini dan backend menolak mulai pembayaran. Pembayaran awal sukses yang baru diterima sesudah batas 2 jam membatalkan booking dan masuk refund manual. Periksa status sesudah pembayaran; aplikasi tidak menjanjikan sukses dari callback.
