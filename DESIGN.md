---
version: alpha
name: CourtBook
description: Papan jadwal klub olahraga Malang dengan ilustrasi garis lapangan dan checkout yang tenang.
colors:
  primary: '#163e32'
  accent: '#d9ef83'
  background: '#f5f7f5'
  surface: '#ffffff'
  text: '#1c3029'
  muted: '#53665d'
  border: '#dce4de'
  danger: '#a93030'
  warning: '#865512'
typography:
  body:
    fontFamily: 'Segoe UI, Arial, sans-serif'
  display:
    fontFamily: 'Bahnschrift, Trebuchet MS, sans-serif'
  mono:
    fontFamily: 'Consolas, monospace'
rounded:
  control: '0.5rem'
  panel: '1rem'
spacing:
  section: '4rem'
  page-max: '74rem'
components:
  button: {}
  field: {}
  status: {}
  dialog: {}
---
# CourtBook Design System

## Overview
CourtBook melayani pelanggan kompleks olahraga di Malang yang ingin memilih jadwal dari ponsel, serta admin operasional dari desktop. Brief pengguna adalah sumber konteks; tidak ada riset pengguna yang diklaim. Bahasa Indonesia, format rupiah integer, Gregorian, Asia/Jakarta. Landing berregister brand; katalog, checkout, profil, dan admin berregister produk. Target aksesibilitas WCAG 2.2 AA, bukan klaim sertifikasi.

North Star: papan jadwal klub tenis, garis putih lapangan, angka jam terbaca, dan tombol tindakan tegas. Signature: ilustrasi lapangan SVG orisinal sedikit miring pada hero; seluruh formulir tetap tenang dan lurus. Alternatif kartu metrik pada hero ditolak karena tidak membantu memilih lapangan. Hindari foto venue yang tidak benar, gradien neon, dan dashboard dekoratif.

## Colors
Hijau primary untuk brand dan tindakan aman; lime accent hanya bola/aksen olahraga. Surface putih pada background abu hijau. Danger untuk pembatalan dan penghapusan; warning untuk batas waktu dan pembayaran belum tersedia. Semua status memiliki label teks. Mode terang saja; forced colors mengikuti sistem.

## Typography
Display Bahnschrift memberi karakter papan olahraga; fallback Trebuchet MS. Body Segoe UI dengan fallback Arial untuk formulir. Consolas untuk kode booking dan jam. Tidak mengunduh font eksternal sehingga tidak ada font swap. Judul 2-4.1rem responsif; body 15px, line-height 1.65; field dan tabel 0.83-0.88rem. Judul marketing lebih besar daripada admin.

## Layout
Lebar 74rem. Hero dua kolom, katalog tiga kartu, detail dua kolom. Breakpoint 1000px dan 720px; semua menjadi satu kolom pada mobile. Admin sidebar berubah menjadi navigasi terbungkus di atas. Dokumen memiliki scroll vertikal; tabel memiliki scroll horizontal sendiri. Ilustrasi mencadangkan aspect ratio; tombol mempertahankan dimensi saat busy. Section gap 4rem desktop dan 2.5rem mobile.

## Elevation & Depth
Gunakan border dan warna permukaan, tanpa shadow pada kartu statis. Backdrop dialog memisahkan keputusan berisiko. Panel checkout sticky desktop dan static mobile.

## Shapes
Control radius 0.5rem, panel 1rem. Badge kecil berbentuk persegi membulat, bola/nomor langkah melingkar. Logo mengikuti garis lapangan.

## Components
Canonical source: CSS custom properties pada resources/css/app.css. DESIGN.md mencerminkan nilai yang diterima; test token drift menghubungkan kedua sumber. Mapping: colors.* ke --color-*, typography.body/display/mono ke --font-body/display/mono, rounded.control/panel ke --radius-control/panel, spacing.section/page-max ke --space-section/--page-max. Blade dan booking.css mengonsumsi variabel tersebut. Nilai role tambahan untuk hover/scrollbar hanya dimiliki CSS.

Button emphasis solid/outline/ghost dan intent primary/neutral/danger. Hover/active, focus-visible 3px, disabled/busy dengan label tetap. Feedback memakai components/feedback.blade.php, role status/alert. Field owner components/field.blade.php dengan label, help/error, aria-invalid; form errors difokuskan. Native select dan date/datetime-local sengaja diterima: popup/format mengikuti OS; label dan keterangan waktu dimiliki produk. Modal memakai native dialog dengan teks milik aplikasi, fokus ke tindakan aman, Escape, inert backdrop dan pemulihan fokus. Tidak memakai window.confirm.

Ikon garis SVG orisinal atau simbol panah dengan teks; ilustrasi bukan foto fasilitas sungguhan. Motion 150ms hanya hover/active; reduced-motion menonaktifkannya. Rupiah tanpa pecahan; waktu selalu WIB. Jadwal membedakan tersedia, dipilih, terisi, maintenance dalam teks dan warna. Loading memblok submit duplikat tanpa menggeser kontrol; kegagalan pembayaran tidak mengklaim sukses.

## Do's and Don'ts
- Gunakan ilustrasi simulasi dan status dengan label.
- Pertahankan layout pelanggan/admin berbeda dengan owner field/feedback/dialog yang sama.
- Jangan memakai lime sebagai warna teks kecil di atas putih.
- Jangan tampilkan key server, payload teknis, atau detail error kanal kepada pelanggan.
