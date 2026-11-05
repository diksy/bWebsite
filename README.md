# blog-ai

Platform blog untuk satu pemilik situs, berbahasa Indonesia, dengan fokus **SEO** dan **penulis AI terjadwal**.
Dibangun dengan Laravel; cocok dijalankan di shared hosting (cPanel) maupun VPS.

> Artikel buatan AI **selalu berstatus Draf**. Tidak ada penerbitan otomatis — Anda yang menyunting dan menerbitkan.

## Fitur

**Situs publik**
- Beranda, halaman artikel, kategori, tag, halaman penulis, dan pencarian — dirender di server (cepat dan ramah mesin pencari).
- Gambar otomatis dikonversi ke WebP, dengan lazy-load.
- SEO: `sitemap.xml`, RSS, `robots.txt`, canonical, meta/Open Graph, data terstruktur JSON-LD (Article, Breadcrumb, FAQ, Person), dan ping IndexNow saat artikel terbit.
- Tampilan dapat diganti: Minimalis (default), Neo Brutalism, Glassmorphism, atau gaya CSS buatan Anda sendiri.

**Panel admin**
- Editor artikel Markdown dengan skor SEO (berbasis aturan, tanpa AI), saran tautan internal, unggah gambar, dan jadwal terbit.
- Pengelolaan kategori, tag, dan pengguna (peran Admin dan Penulis).
- Path panel (`/admin`) dan path artikel (`/artikel`) dapat diubah.
- Login kata sandi, atau opsional lewat SSO (OIDC) — bila SSO diwajibkan, hanya pengguna yang sudah terdaftar yang bisa masuk.

**Penulis AI**
- Menggunakan layanan AI mana pun yang kompatibel dengan OpenAI (isi base URL, API key, dan nama model) serta pencarian web (default: Tavily).
- Anda membuat *tema terjadwal*: tema atau kata kunci, frekuensi, kategori tujuan, penulis, dan batas draf per minggu.
- Setiap tema diproses bertahap: cari web → ringkas sumber → outline → draf lengkap dengan meta dan FAQ. Sumber dicatat di artikel.
- Log AI mencatat token terpakai dan error per tugas.

## Kebutuhan

- PHP 8.3 atau lebih baru, dengan ekstensi **GD berdukungan WebP** (periksa dengan `php -m`), serta ekstensi umum Laravel (mbstring, openssl, pdo, dll.)
- Composer, serta Node.js dan npm (untuk membangun aset sekali saat pemasangan)
- Database SQLite (bawaan) atau MySQL/MariaDB
- Akses terminal ke server dan kemampuan menambah satu cron job

## Pemasangan

```bash
git clone https://github.com/diksy/bWebsite.git
cd bWebsite
composer install --no-dev --optimize-autoloader
npm ci && npm run build
cp .env.example .env
php artisan key:generate
```

Sunting `.env`: setidaknya `APP_NAME`, `APP_URL` (alamat situs, mis. `https://contoh.id`), `APP_ENV=production`, `APP_DEBUG=false`, dan pengaturan database. Nilai `QUEUE_CONNECTION`, `SESSION_DRIVER`, dan `CACHE_STORE` sudah `database`.

Lalu siapkan database dan akun admin pertama:

```bash
php artisan migrate --seed
php artisan storage:link
php artisan app:buat-admin email@contoh.id "Nama Admin"   # mencetak kata sandi sementara
```

Masuk di `https://situs-anda/admin`, lalu segera ganti kata sandi.

> **Penting:** jangan mengganti `APP_KEY` setelah API key (AI, pencarian, SSO) disimpan — nilainya terenkripsi dengan kunci itu dan harus diisi ulang bila kunci berubah.

### Shared hosting (cPanel)

1. Unggah proyek ke folder di luar web root bila bisa. Domain harus menunjuk ke folder `public`. Bila hosting hanya membaca `public_html`, repo ini menyertakan symlink `public_html → public`.
2. Pastikan versi PHP domain 8.3+ dan ekstensi GD (dengan WebP) aktif (menu *Select PHP Version* / *MultiPHP*).
3. Jalankan perintah pemasangan di atas lewat Terminal cPanel (atau bangun aset di komputer Anda lalu unggah folder `public/build`).
4. Tambahkan **satu** cron job, setiap menit:

```
* * * * * cd /home/USER/folder-aplikasi && /usr/local/bin/php artisan schedule:run >> /dev/null 2>&1
```

Cron ini menjalankan semuanya: antrean tugas AI, penerbitan artikel terjadwal, dan pembuatan draf dari tema per jam. Tanpa cron ini, penulis AI dan jadwal terbit tidak berjalan.

Izin tulis diperlukan pada `storage/` dan `bootstrap/cache/`.

### Menjalankan di komputer sendiri

```bash
composer install && npm ci && npm run build
cp .env.example .env && php artisan key:generate
php artisan migrate --seed
php artisan app:buat-admin email@contoh.id "Nama Admin"
php artisan serve
```

## Penggunaan

1. **Pengaturan situs** — nama situs, tagline, path panel dan path artikel, IndexNow, serta SSO (opsional).
   Saat mengganti path artikel, alamat lama dialihkan otomatis (301).
2. **Pengaturan AI** — isi base URL, API key, dan model layanan AI Anda, lalu tekan *Uji koneksi*. Isi juga API key pencarian web.
3. **Kategori & tag** — buat kategori tujuan sebelum membuat tema.
4. **Tema terjadwal** — tambahkan tema, frekuensi, kategori, dan batas draf per minggu. Tombol *Jalankan* membuat draf saat itu juga.
5. **Artikel** — periksa draf dari AI di editor, perbaiki berdasarkan skor SEO dan saran tautan, lalu terbitkan atau jadwalkan.
6. **Tampilan** — pilih gaya situs. Untuk gaya sendiri: unduh `template-contoh.css`, ubah nilai variabelnya, lalu unggah (CSS saja, maks. 100 KB).
   `@import`, `url()`, `expression()`, dan karakter `<` ditolak demi keamanan. Menghapus gaya yang sedang aktif mengembalikan situs ke Minimalis.
7. **Log AI** — pantau pemakaian token dan error.

## Login SSO (opsional)

Di *Pengaturan situs* aktifkan SSO, isi Base URL, Client ID, dan Client secret dari penyedia OIDC Anda (didukung: [BikininSSO](https://github.com/diksy/bSSO)). Daftarkan alamat callback, post-logout, dan back-channel logout yang ditampilkan di halaman itu pada penyedia SSO. Akun tidak dibuat otomatis: pengguna harus lebih dulu didaftarkan admin (dicocokkan lewat email).

## Pembaruan

```bash
git pull
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan optimize:clear && php artisan optimize
```

## Pemecahan masalah

| Gejala | Penyebab umum |
|---|---|
| Tulisan `@livewireStyles` / `@livewire(...)` tampil di halaman | Cache lama. Jalankan `php artisan optimize:clear`. |
| Gambar tidak jadi WebP / unggah gagal | Ekstensi GD tanpa dukungan WebP. Aktifkan lalu periksa `php -r 'var_dump(gd_info()["WebP Support"]);'`. |
| Draf AI tidak pernah muncul | Cron `schedule:run` belum terpasang, atau pengaturan AI/pencarian belum diuji. Lihat *Log AI*. |
| Gambar/gaya kustom 404 | `php artisan storage:link` belum dijalankan. |
| API key hilang setelah pindah server | `APP_KEY` berbeda dari sebelumnya. Isi ulang key di panel. |

## Pengembangan

```bash
php artisan test            # tes (Pest)
vendor/bin/phpstan analyse  # analisis statis (Larastan)
vendor/bin/pint             # gaya kode
```
