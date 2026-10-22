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

## Guided customer booking
Alur katalog → jadwal → periksa pesanan → bayar memakai booking-steps.blade.php dengan empat langkah yang sesuai proses nyata. Kalender dan pilihan waktu mendahului informasi fasilitas. Desktop memakai pilihan di kiri dan ringkasan di kanan; ponsel menumpuk dalam urutan yang sama. Jadwal memakai radio native berlabel jam, checkout memakai kartu radio uang muka/lunas. Identitas warna dan tipografi tetap mengikuti token yang ada. CSS tambahan dibatasi pada .booking-flow di resources/css/booking.css; home dan admin tetap mengikuti tampilan sebelumnya. Ketentuan, riwayat, dan pembatalan berada di disclosure native, sementara nominal, batas waktu, serta status pembayaran selalu terlihat.

## Do's and Don'ts
- Gunakan ilustrasi simulasi dan status dengan label.
- Pertahankan layout pelanggan/admin berbeda dengan owner field/feedback/dialog yang sama.
- Jangan memakai lime sebagai warna teks kecil di atas putih.
- Jangan tampilkan key server, payload teknis, atau detail error kanal kepada pelanggan.

## Home page blue refresh

The user requested a blue sports identity and a clearer first-time visitor path, scoped to the homepage on branch `design/home-blue-refresh`. The supplied pickleball/tennis presentation images guide the court photography, condensed athletic type and white space. This is an intentional home pilot; existing global frontmatter tokens remain the application theme for booking, account and admin routes; the catalogue now joins the blue shell by explicit user request.

Runtime ownership remains Model B. `resources/css/home.css`, loaded by the shared home/catalogue shell `layouts/home.blade.php`, remaps semantic tokens under `.home-page`: primary `#1455d9`, accent `#d6f05a`, background/surface `#ffffff`, text `#102c59`, muted `#526581`, border `#dce5f0`; home tint is `#edf4ff`. These variables feed links, focus, shared account actions and the inherited scrollbar baseline. No unrelated route receives this stylesheet.

Home typography uses locally hosted Manrope (body, navigation, prices) and Barlow Condensed 700 (hero/section headings), with explicit preload and Latin/Indonesian fallbacks. Typeface sources and licenses are in `resources/fonts`; Vite emits versioned assets. The 76rem grid has one photographic hero, a plain operational-information strip, dynamic court cards, a three-step explanation, FAQ disclosures and a restrained simulation notice. Court photos reserve aspect ratio and are labeled as illustrations. Native disclosures own the mobile navigation and FAQ; the account links still come from the shared `customer-account-links.blade.php` owner used by the original customer layout. `.home-theme` remaps the inherited root scrollbar palette so the shared baseline follows the blue home theme.

For layout exploration, rejected patterns and visual review, see `docs/home-refresh-design.md`. Generated asset provenance and prompts: `docs/home-refresh-assets.md`. The application behavior and booking/payment rules in UX-CONTRACT.md remain authoritative.

The home footer includes a quiet payment-method band: original BCA, BRI/BRIVA, Mandiri, BNI, BSI, GoPay, DANA and QRIS assets sourced from Midtrans, each on an equal white tile without interactive styling. The grid adapts from eight columns to four, then two on the narrowest screens. Brand colors remain intact, and alt text names each service. The caption defers actual method availability to checkout; adding the logos does not activate payment channels. Asset provenance is recorded in `public/images/payments/sources.json`.

Home shell ownership: `layouts/home.blade.php` composes `<x-home.navbar/>` and `<x-home.footer/>`. Their maintained markup lives in `components/home/navbar.blade.php` and `components/home/footer.blade.php`; the footer includes `components/home/payment-methods.blade.php` for payment brands. Shared account links remain in `components/customer-account-links.blade.php`, and existing visual rules remain in `resources/css/home.css`. Edit the owning component rather than copying shell markup into page content.

Featured court cards are owned by `components/home/court-card.blade.php`. The sports-poster variant gives the photograph and Barlow Condensed court name visual priority, with a contrast overlay behind white text. A compact white panel holds actual facilities, a larger price with an explicit per-hour label, and a solid blue schedule action. The sport icon names a real category; no rating, promotion or availability is fabricated. This replaces the original equal image/paragraph card composition while retaining the home palette, route destinations, image disclaimer and server-owned pricing.

The homepage booking guide is owned by `components/home/booking-guide.blade.php`. The accepted minimal variant keeps the navy field and athletic heading, with three open numbered steps and one section divider. Step headings are short; each has one paragraph. No cell borders, badges, icons, rails or secondary captions. Mobile stacks the same order. Login, dynamic DP/hold/balance deadlines and payment verification remain explicit; the catalogue action reuses the home light button.

The homepage navbar stays at the viewport top using CSS sticky positioning and an opaque white surface. Document scroll padding reserves the desktop/mobile navbar height for anchor and keyboard navigation. The mobile disclosure closes on navigation and scrolls internally on short screens.

## Blue court catalogue

The user extended the accepted home theme to /courts. It reuses layouts/home, the sticky home navbar/footer and home court-card, with a pale blue heading and a quiet white search area. Current-route markers and guide links work on both routes. Booking stages retain their canonical component; fields, native sport select, validation, explicit GET search and nine-result pagination retain the existing contracts. The catalogue pagination view `components/home/pagination.blade.php` uses the same server paginator and query-preserving URLs with Indonesian labels. It is the blue-shell variant; existing application pagination is retained elsewhere. Photos retain their illustration disclaimer. Schedule and checkout are outside this migration.

## Blue login

/login uses the shared blue shell and a split court photograph/form composition, owned by auth/login.blade.php and scoped sports-login classes in home.css. The optional inlineFeedback layout parameter moves the canonical feedback component into the form column. The photo is decorative and labeled as an illustration; it is omitted on narrow screens so the form comes first. Inputs, error associations, password visibility and busy state reuse the canonical field/feedback/app.js owners. No authentication/session/permission behavior changed; other auth modes retain auth/form.blade.php.

## Blue booking history

/bookings joins the shared blue shell. Customer history uses customer/booking-summary.blade.php: open schedule rows with snapshot court name/code, native status badges, date/time/duration, active booking payment deadlines, total and a detail link. Mobile stacks identity and schedule above total/action. The shared blue pagination now accepts itemLabel/paginationLabel for catalogue or bookings, retaining server queries. Status filtering, ten-row pagination, newest-first order, expiry and customer-only ownership remain server contracts. Empty-filter recovery and first-booking guidance are distinct. Admin tables and the booking detail/checkout retain their existing owners.

## Blue court schedule

/courts/{court} now uses the blue shell and court-schedule variant of the canonical booking-flow. A compact court identity/photo header precedes date, duration and native time radios. The photo is labeled as an illustration and omitted on narrow screens. Active slots use blue, unavailable slots carry explicit reason text, and maintenance uses the shared warning semantics. The summary sits below the sticky navbar on tall desktops and stacks naturally on mobile or short viewports. Fields, status/validation feedback, data hooks and availability calculations remain owned by the existing booking components and booking.js. The checkout is not part of this visual migration.
