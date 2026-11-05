# blog-ai — Spesifikasi

## Tujuan
Platform blog single-customer (Laravel, Bahasa Indonesia, domain/subdomain sendiri mis. storika.id) dengan prioritas SEO dan penulis AI terjadwal. Semua hasil AI berstatus Draf; tidak ada auto-publish.

## Pengguna
- Pengunjung publik (pembaca, mesin pencari).
- Penulis: menyunting draf, unggah gambar, jadwalkan terbit.
- Admin: semua akses penulis + pengaturan AI, tema terjadwal, log AI, kelola pengguna.

## Halaman / fitur
1. Publik (SSR cepat, WebP, lazy-load): beranda, artikel, kategori/tag, halaman penulis, pencarian; sitemap.xml, RSS, robots.txt, canonical, meta/OG; JSON-LD Article/Breadcrumb/FAQ/Person; ping IndexNow saat terbit.
2. Pengaturan AI: base URL + API key kompatibel OpenAI (terenkripsi), nama model, uji koneksi; API key pencarian web (adapter, default Tavily).
3. Tema terjadwal: tema/kata kunci, frekuensi, kategori tujuan, batas draf/minggu.
4. Pipeline job bertahap: cari web → ringkas sumber → outline → draf + meta + FAQ; sumber dicatat; status selalu Draf.
5. Editor: skor SEO berbasis aturan (tanpa AI), saran link internal, unggah gambar → WebP, jadwal terbit.
6. Log AI: token terpakai & error per job.
7. Teknis: cron cPanel → schedule:run; queue database (queue:work --stop-when-empty); job kecil per tahap (aman batas waktu shared hosting). Login admin + penulis.

## Di luar cakupan
Multi-tenant/langganan, multi-bahasa, komentar, auto-publish.

## Keputusan
- Admin/editor: Blade + Livewire.
- Editor artikel: Markdown.
- Penulis AI dipilih per tema (tiap tema punya penulis tujuan).
