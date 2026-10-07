# UX Contract

## Product context
Pelanggan adalah role fokus praktikum; admin pendukung. Bahasa id-ID, rupiah integer, Gregorian, Asia/Jakarta. WCAG 2.2 AA sebagai target. Lihat DESIGN.md untuk identitas visual. Brief pengguna menjadi sumber aturan; tidak ada PDF modul/template yang tersedia saat implementasi.

## Business-context sources
| Domain / scope | Authoritative source | Source type | Reviewed date |
|---|---|---|---|
| Permission model | docs/requirements.md FR-AUTH-01, FR-BOOK-02 | Brief pengguna | 2026-10-07 |
| Data lifecycle | docs/business-rules.md BR-01 sampai BR-12 | Brief pengguna | 2026-10-07 |
| Deletion / retention | docs/architecture.md | Keputusan implementasi untuk menjaga riwayat | 2026-10-07 |
| Billing / payment | docs/midtrans-sandbox.md dan docs/business-rules.md | Brief dan dokumentasi resmi Midtrans | 2026-10-07 |
| Ketentuan demo | docs/business-rules.md | Aturan pengguna; persetujuan asisten belum ada | 2026-10-07 |
| Market / content | DESIGN.md Overview | Brief Indonesia/Malang | 2026-10-07 |

## Canonical UI Map
| Capability | Canonical owner | Source of truth | Allowed variants | Verification |
|---|---|---|---|---|
| Select/Listbox | Native select pada Blade | UX-CONTRACT.md | native; popup platform diterima | keyboard/browser + HTTP validation |
| Date | Native date/datetime-local melalui components/field.blade.php | UX-CONTRACT.md | native; popup/locale OS diterima, label WIB | browser + batas server |
| Form | components/field.blade.php, feedback.blade.php, resources/js/app.js | UX-CONTRACT.md | create/edit/payment | AuthAndAdminTest + browser |
| Scrollbar | resources/css/app.css global baseline | DESIGN.md | tabel overflow horizontal | computed browser style |
| Toast | components/feedback.blade.php | UX-CONTRACT.md | persistent status/alert; tanpa transient toast | HTTP + live region browser |
| CRUD | Controller Admin + Form Request + BookingService | docs/business-rules.md | katalog kembali list; pengaturan/maintenance tetap halaman | AuthAndAdminTest + browser |

## Dataset navigation
Admin tabel 15 baris, booking pelanggan 10, katalog 9. Search/filter/page melalui query URL. Search memakai submit eksplisit (tidak ada live remote search/race); clear segera submit query kosong. Tanpa bulk selection/sort frontend. Tabel dapat digulir horizontal. Empty/no-results memberi arah tindakan, feedback error menetap. Pagination memakai label Indonesia.

## Flow ledger
| Operation | Trigger | Pending | Success destination | Success feedback | Failure recovery | Focus outcome | Source ref |
|---|---|---|---|---|---|---|---|
| Create court | Simpan lapangan | tombol disabled/aria-busy | Daftar lapangan | status berhasil | input dipertahankan | ringkasan error | requirements FR-ADM-01 |
| Edit court | Simpan lapangan | tombol disabled | Daftar lapangan | tarif baru | server validation | ringkasan error | BR-05 |
| Delete court | Dialog hapus | fokus aman, submit disabled | daftar | status dihapus | riwayat melarang hapus, arahkan nonaktif | pemulihan fokus dialog | architecture retention |
| Create booking | Booking jadwal ini | submit disabled | Detail checkout | batas 15 menit | konflik slot, input dipertahankan | ringkasan error | BR-01 sampai BR-06 |
| Cancel booking | Dialog batalkan | fokus aman | detail | refund manual bila layak | batas 24 jam | pemulihan fokus | BR-10 |
| Payment | Setuju ketentuan dan lanjut | blok percobaan duplikat; periksa server otomatis | Snap lalu detail otomatis setelah status final | DP berhasil/lunas hanya setelah verifikasi; refund terpisah | polling 5 detik maksimal 12 pemeriksaan, retry manual; uncertain tetap diblok | pesan status aria-live | BR-07 sampai BR-09 |
| Search | tombol Cari/clear | submit disabled | query URL | jumlah hasil | validasi teks/filter | field atau ringkasan | FR-COURT-01 |
| Profile | Simpan profil | submit disabled | profil | status tersimpan | kata sandi saat ini wajib untuk ubah password | ringkasan | FR-PROFILE-01 |

## Navigation and responsive behavior
Title tiap route bahasa Indonesia. Error 403/404/419/429/500 memiliki halaman dengan navigasi. Native input/select sesuai keyboard platform. Sidebar admin wrap pada 720px; detail/checkout stack, tabel tetap scroll. Tidak memotong kode transaksi (overflow-wrap). Sticky hanya checkout desktop. Back menggunakan link nyata.

## Overlays and feedback
Native dialog aplikasi, fokus awal tombol kembali, Escape menutup, browser menjaga fokus/inert background. app.js memulihkan trigger. Konfirmasi pembatalan, penghapusan, pelepasan maintenance, pengaturan operasional/refund. Tidak ada konfirmasi save profil/katalog rutin. Data-dirty pada edit profil/katalog/maintenance: dialog in-app; beforeunload hanya lifecycle unload. Feedback critical inline, bukan transient toast. Password masked, tombol show/hide dengan aria-pressed.

## Validation, async and recovery
novalidate; Laravel Form Request/service otoritatif. Nilai non-secret dipertahankan, password dikosongkan, error summary role alert dan autofocus. Field terpusat punya relasi label/error; validasi bisnis lintas field tampil di summary. Request mutasi tidak diulang optimistis. Timeout pembayaran: jangan menandai gagal/sukses; attempt uncertain untuk reconcile. Gangguan jaringan: retry/periksa status, backend mencegah double booking/payment. Tidak ada draft offline atau auto-save. Session expired punya pesan 419; input rahasia tidak dipersistenkan. State busy reset melalui pageshow untuk browser Back.
