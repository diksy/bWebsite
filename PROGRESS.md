# blog-ai — Progres

## Prototype (data dummy, Blade + Tailwind)
- [x] Layout dasar + navigasi publik (mobile)
- [x] Beranda
- [x] Halaman artikel (FAQ, penulis, breadcrumb)
- [x] Kategori / tag
- [x] Halaman penulis
- [x] Pencarian
- [x] Login (admin + penulis)
- [x] Dashboard admin
- [x] Daftar artikel + filter status
- [x] Editor Markdown + panel skor SEO + saran link internal
- [x] Unggah gambar + jadwal terbit
- [x] Pengaturan AI (OpenAI-compatible + Tavily + uji koneksi)
- [x] Tema terjadwal (daftar + form)
- [x] Log AI (token & error per job)

## Revisi 2026-11-05
- [x] Path /admin dapat diubah di panel admin
- [x] Path /artikel dapat diubah di panel admin
- [x] Opsi integrasi SSO (BikininSSO, opsional) di pengaturan + tombol di login

## Tambahan 2026-11-05
- [x] Manajemen pengguna (CRUD, admin saja); SSO penuh = hanya pengguna terdaftar yang bisa masuk

## Build (migration + model + Form Request + tes Pest + sambung view)
- [x] Pondasi: auth (admin/penulis), tabel users+peran, middleware peran, path admin & artikel dinamis (settings)
- [x] Pengguna: CRUD admin (Form Request, tes)
- [x] SSO opsional BikininSSO (OIDC+PKCE, hanya pengguna terdaftar, back-channel logout)
- [x] Pengaturan: tabel settings, API key terenkripsi, uji koneksi AI & Tavily (adapter)
- [x] Kategori, tag, penulis (profil) + seeder
- [x] Artikel: model, status draf/terjadwal/terbit, editor Markdown, FAQ, sumber
- [x] Upload gambar → WebP
- [x] Skor SEO berbasis aturan + saran link internal
- [x] Publik: beranda, artikel, kategori/tag, penulis (SSR, lazy-load)
- [x] Pencarian
- [x] SEO teknis: meta/OG, canonical, JSON-LD, sitemap.xml, RSS, robots, redirect path lama
- [x] Terbit terjadwal (schedule) + ping IndexNow
- [x] Tema terjadwal (CRUD, batas draf/minggu)
- [x] Pipeline AI: job per tahap (cari web → ringkas → outline → draf+meta+FAQ), selalu Draf
- [x] Log AI (token & error per job) + dashboard
- [x] Cron/queue database untuk cPanel, hapus app/Prototype, cek akhir

## Siklus 2 — Gaya desain UI (2026-11-05)
Asumsi: gaya berlaku untuk situs publik (panel admin tetap); gaya kustom = satu berkas `.css` (variabel desain + override) yang dibersihkan sebelum dipakai.

### Prototype (data dummy)
- [x] Admin: halaman "Tampilan" — pilih gaya (kartu preview: Minimalis [default], Neo Brutalism, Glassmorphism, + gaya kustom terunggah), tombol Terapkan
- [x] Preview publik (beranda + artikel) per gaya lewat `?gaya=` dummy, untuk 3 gaya bawaan
- [x] Admin: unggah gaya kustom (form, daftar gaya, hapus) + unduh template CSS contoh beranotasi
- [x] Tautan menu "Tampilan" di panel; tampilan mobile

### Build (setelah /setuju prototype)
- [ ] Migration/model gaya + setelan gaya aktif; gaya bawaan sebagai berkas CSS dengan variabel (token) desain
- [ ] Layout publik memuat CSS gaya aktif; Form Request unggah (ukuran, ekstensi, sanitasi: tanpa @import/url eksternal/expression)
- [ ] Template contoh dapat diunduh; tes Pest (pilih gaya, unggah valid/invalid, sanitasi, default minimalis)

## Revisi 2026-11-05 (UAT siklus 2)
- [x] Bug: `@livewireStyles` / `@livewire(...)` tampil sebagai teks di staging — provider Livewire didaftarkan eksplisit di `bootstrap/providers.php` (cache discovery server basi) + tes
