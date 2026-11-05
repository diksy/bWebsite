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
- [ ] Pengguna: CRUD admin (Form Request, tes)
- [ ] SSO opsional BikininSSO (OIDC+PKCE, hanya pengguna terdaftar, back-channel logout)
- [ ] Pengaturan: tabel settings, API key terenkripsi, uji koneksi AI & Tavily (adapter)
- [ ] Kategori, tag, penulis (profil) + seeder
- [ ] Artikel: model, status draf/terjadwal/terbit, editor Markdown, FAQ, sumber
- [ ] Upload gambar → WebP
- [ ] Skor SEO berbasis aturan + saran link internal
- [ ] Publik: beranda, artikel, kategori/tag, penulis (SSR, lazy-load)
- [ ] Pencarian
- [ ] SEO teknis: meta/OG, canonical, JSON-LD, sitemap.xml, RSS, robots, redirect path lama
- [ ] Terbit terjadwal (schedule) + ping IndexNow
- [ ] Tema terjadwal (CRUD, batas draf/minggu)
- [ ] Pipeline AI: job per tahap (cari web → ringkas → outline → draf+meta+FAQ), selalu Draf
- [ ] Log AI (token & error per job) + dashboard
- [ ] Cron/queue database untuk cPanel, hapus app/Prototype, cek akhir
