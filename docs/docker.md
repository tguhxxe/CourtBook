# Docker untuk CourtBook

Compose memakai `docker/local.Dockerfile` untuk lingkungan lokal Linux/Windows. `Dockerfile` di root tetap dipakai untuk hosting Render; kedua konfigurasi berjalan terpisah.

## Persiapan sekali per komputer

- Linux: Docker Engine dengan Compose plugin, atau Docker Desktop.
- Windows: [Docker Desktop](https://docs.docker.com/desktop/setup/install/windows-install/), backend WSL 2, mode Linux containers. Jalankan Docker Desktop sebelum menjalankan proyek. Restart Windows jika diminta saat instalasi.
- Git untuk clone/pull kode. Jalankan `docker info` dan `docker compose version` untuk memeriksa kesiapan Docker. PHP, Composer, Node, Laragon, dan server database di host tidak diperlukan.

## Clone dan jalankan

Clone repository dari GitHub, masuk ke folder CourtBook, lalu jalankan perintah yang sama di terminal Linux maupun PowerShell:

```sh
docker compose up --build -d
docker compose ps
```

Buka http://127.0.0.1:8000 setelah `app` healthy. Akun pelanggan: `pelanggan@courtbook.test`; akun admin: `admin@courtbook.test`. Password keduanya `CourtBook123!`.

Build pertama mengunduh image dan dependency sehingga memerlukan internet. Build berikutnya memanfaatkan cache. Aplikasi dan scheduler berjalan sebagai pengguna non-root. Scheduler baru mulai setelah aplikasi siap. `artisan serve` dipakai untuk pengembangan/demo lokal.

Docker memakai `.env` internal dalam volume, dengan key yang dibuat otomatis. `.env` host tidak disalin ke image. Compose hanya meneruskan variabel port dan Midtrans yang disebut dalam `compose.yaml`; konfigurasi PHP/database host tidak memengaruhi container. Jangan membagikan konfigurasi rahasia atau memasukkannya ke Git.

## Setelah mengubah kode atau git pull

```sh
docker compose up --build -d
```

Kode tidak dipasang sebagai bind mount agar tidak bergantung pada izin/path filesystem Linux atau Windows. Rebuild diperlukan untuk menerapkan perubahan PHP, Blade, CSS, JS, atau dependency. Migrasi baru dijalankan saat app mulai. Seed dijalankan sekali pada volume baru, sehingga restart tidak mengembalikan data/password yang telah diubah. APP_KEY tetap sama selama volume dipertahankan.

## Perintah harian

```sh
docker compose logs --tail=100 app scheduler
docker compose down
docker compose up -d
docker compose exec app php artisan migrate:status
docker compose exec app php artisan test
```

Pengujian Laravel memakai database uji terisolasi sesuai `phpunit.xml`. Node hanya berada di tahap build aset; perintah npm di host tidak diperlukan untuk menjalankan aplikasi. Untuk pengembangan dengan Vite hot reload, gunakan instalasi manual yang dijelaskan di README atau tambahkan konfigurasi development tersendiri.

## Port 8000 sudah dipakai

Buat atau tambahkan baris berikut di `.env` pada folder proyek:

```dotenv
COURTBOOK_PORT=8001
```

Jalankan ulang `docker compose up -d`, lalu buka http://127.0.0.1:8001. Compose membaca nilai tersebut dari `.env` host. Port hanya dipublikasikan pada loopback komputer.

## Midtrans Sandbox (opsional)

Untuk menjalankan demo tampilan/login, key Midtrans tidak diperlukan. Untuk pembayaran Sandbox, isi variabel berikut pada `.env` host; lihat juga [panduan Midtrans](midtrans-sandbox.md):

```dotenv
MIDTRANS_SERVER_KEY=
MIDTRANS_CLIENT_KEY=
MIDTRANS_MERCHANT_ID=
MIDTRANS_PUBLIC_URL=
```

Jalankan ulang `docker compose up -d` agar environment app dan scheduler diperbarui. Key tidak dimasukkan ke image atau Git. Tanpa key, booking tidak dikonfirmasi melalui pembayaran dan akan expired sesuai aturan aplikasi.

## Data saat pindah komputer

Named volumes bertahan ketika container dihentikan/dibuat ulang, tetapi tidak ikut Git. Instalasi Docker juga tidak membaca database atau `.env` instalasi manual yang sudah ada. Pertahankan nama proyek Compose `courtbook` agar volume yang sama dipakai kembali pada komputer tersebut.

Jika hanya perlu menjalankan proyek di komputer baru, clone dan jalankan seperti biasa: data demo dibuat otomatis. Jika ingin membawa database, key, dan file yang sudah ada di Docker, lakukan backup berikut saat aplikasi tidak dipakai:

```sh
docker compose stop
docker compose run -d --name courtbook-backup --no-deps app sleep 3600
docker exec courtbook-backup tar -czf /tmp/courtbook-backup.tar.gz -C / data app/storage
docker cp courtbook-backup:/tmp/courtbook-backup.tar.gz ./courtbook-backup.tar.gz
docker stop courtbook-backup
docker rm courtbook-backup
docker compose up -d
```

Pindahkan arsip secara privat. Arsip berisi database, APP_KEY, dan log yang mungkin sensitif; jangan commit ke GitHub. Di komputer tujuan, jalankan proyek sekali untuk menyiapkan volume, hentikan, lalu pulihkan:

```sh
docker compose stop
docker compose run -d --name courtbook-restore --no-deps app sleep 3600
docker cp ./courtbook-backup.tar.gz courtbook-restore:/tmp/courtbook-backup.tar.gz
docker exec courtbook-restore tar -xzf /tmp/courtbook-backup.tar.gz -C /
docker stop courtbook-restore
docker rm courtbook-restore
docker compose up -d
```

Restore mengganti file data dengan isi backup. Gunakan pada volume tujuan baru/kosong; backup dulu bila tujuan sudah berisi data. Prosedur ini untuk data yang sudah berada di Docker; migrasi data instalasi manual perlu memindahkan database dan APP_KEY asal secara khusus.

## Troubleshooting

- `Cannot connect to the Docker daemon`: jalankan Docker Desktop/service Docker dahulu.
- `permission denied ... docker.sock` di Linux: pastikan pengguna diizinkan mengakses daemon sesuai petunjuk instalasi Docker pada distro tersebut.
- Port sudah dipakai: hentikan server Laravel manual atau ubah `COURTBOOK_PORT`.
- Build gagal mengunduh image/package: periksa koneksi internet, proxy, dan akses registry Docker/npm/Composer.
- App gagal mulai: baca `docker compose logs --tail=100 app`. Jangan menghapus volume untuk menyelesaikan masalah startup.
- Jangan gunakan `docker compose down -v` kecuali memang ingin menghapus seluruh data Docker CourtBook.
