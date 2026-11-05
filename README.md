# blog-ai

Platform blog satu customer (Laravel, Bahasa Indonesia) dengan prioritas SEO dan penulis AI terjadwal.
Hasil AI selalu berstatus **Draf** — tidak ada auto-publish.

## Ringkas
- Publik SSR: beranda, artikel, kategori/tag, penulis, pencarian; sitemap.xml, `rss.xml`, robots.txt, canonical, meta/OG, JSON-LD (Article, BreadcrumbList, FAQPage, Person), ping IndexNow saat terbit.
- Panel (`/admin`, path bisa diganti di *Pengaturan situs*): editor Markdown (Livewire) dengan skor SEO berbasis aturan, saran link internal, unggah gambar → WebP, jadwal terbit; pengguna (admin/penulis); tema terjadwal; pengaturan AI; log AI; login SSO BikininSSO opsional.
- Penulis AI: pipeline antrean bertahap (cari web → ringkas → outline → draf + meta + FAQ), sumber dicatat.

## Pemasangan lokal
```
composer install && npm ci && npm run build
cp env.contoh ke berkas environment, lalu: php artisan key:generate
php artisan migrate --seed
php artisan app:buat-admin email@contoh.id "Nama Admin"   # mencetak kata sandi sementara
php artisan test && vendor/bin/phpstan analyse
```
(Berkas contoh environment: `.env.example`.)

## cPanel (shared hosting)
1. Deploy lewat `kantor-deploy staging` (rsync; `php artisan migrate --force` + `optimize` dijalankan otomatis).
2. Buat berkas environment di server (tidak ikut deploy): `APP_KEY`, `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL`, kredensial DB, `QUEUE_CONNECTION=database`, `SESSION_DRIVER=database`, `CACHE_STORE=database`.
3. Docroot domain harus `public_html`; repo menyertakan symlink `public_html → public` dan `public/storage → ../storage/app/public`.
4. Pertama kali: `php artisan migrate --seed` lalu `php artisan app:buat-admin ...` (lewat Terminal cPanel).
5. Satu cron (tiap menit):
   `* * * * * cd /home/USER/folder-aplikasi && /usr/local/bin/php artisan schedule:run >> /dev/null 2>&1`
   `schedule:run` sudah menjalankan `queue:work --stop-when-empty` (antrean database), terbit terjadwal, dan tema AI per jam.
6. Setelah mengubah path admin/artikel di panel, cache rute dibangun ulang otomatis.

## Catatan operasional
- Job AI dipecah satu panggilan per tahap (timeout 120 dtk, 2 percobaan) agar aman dari batas waktu hosting.
- Konversi WebP memakai ekstensi **GD** dengan dukungan WebP (cek `php -m`).
- IndexNow aktif otomatis hanya di `APP_ENV=production`; bisa diatur di *Pengaturan situs*.
- API key (AI, pencarian, SSO) disimpan terenkripsi dengan `APP_KEY` — jangan ganti `APP_KEY` tanpa mengisi ulang key.
