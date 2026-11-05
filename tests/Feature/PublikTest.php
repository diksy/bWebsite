<?php

use App\Models\Artikel;
use App\Models\Kategori;
use App\Models\Tag;
use App\Models\User;
use App\Support\Settings;

it('beranda menampilkan artikel terbit saja', function () {
    Artikel::factory()->terbit()->create(['judul' => 'Artikel Terbit Satu']);
    Artikel::factory()->create(['judul' => 'Artikel Masih Draf']);
    Artikel::factory()->create(['judul' => 'Artikel Terjadwal', 'status' => 'terjadwal', 'terbit_pada' => now()->addDay()]);

    $this->get('/')->assertOk()->assertSee('Artikel Terbit Satu')->assertDontSee('Artikel Masih Draf')->assertDontSee('Artikel Terjadwal');
});

it('beranda kosong tetap sukses', function () {
    $this->get('/')->assertOk()->assertSee('Belum ada artikel');
});

it('halaman artikel tampil lengkap dengan isi, FAQ, tag, dan sumber', function () {
    $tag = Tag::factory()->create(['nama' => 'seo']);
    $a = Artikel::factory()->terbit()->create([
        'judul' => 'Panduan Lengkap', 'slug' => 'panduan-lengkap', 'isi' => "## Bagian Satu\n\nTeks isi.",
        'faq' => [['q' => 'Apa itu?', 'a' => 'Jawabannya.']], 'sumber' => [['judul' => 'Sumber A', 'url' => 'https://a.test']],
    ]);
    $a->tag()->attach($tag);

    $this->get('/artikel/panduan-lengkap')->assertOk()
        ->assertSee('Panduan Lengkap')->assertSee('<h2>Bagian Satu</h2>', false)->assertSee('Apa itu?')->assertSee('#seo')->assertSee('Sumber A');
});

it('draf dan artikel terjadwal masa depan menghasilkan 404', function () {
    Artikel::factory()->create(['slug' => 'draf-rahasia']);
    Artikel::factory()->create(['slug' => 'nanti', 'status' => 'terbit', 'terbit_pada' => now()->addDay()]);

    $this->get('/artikel/draf-rahasia')->assertNotFound();
    $this->get('/artikel/nanti')->assertNotFound();
    $this->get('/artikel/tidak-ada')->assertNotFound();
});

it('halaman kategori, tag, dan penulis menampilkan artikelnya', function () {
    $kat = Kategori::factory()->create(['nama' => 'Bisnis', 'slug' => 'bisnis']);
    $tag = Tag::factory()->create(['nama' => 'umkm', 'slug' => 'umkm']);
    $pen = User::factory()->create(['name' => 'Sari Wulandari', 'slug' => 'sari-wulandari']);
    $a = Artikel::factory()->terbit()->create(['judul' => 'Tentang UMKM', 'kategori_id' => $kat->id, 'user_id' => $pen->id]);
    $a->tag()->attach($tag);
    Artikel::factory()->terbit()->create(['judul' => 'Tidak Terkait']);

    $this->get('/kategori/bisnis')->assertOk()->assertSee('Tentang UMKM')->assertDontSee('Tidak Terkait');
    $this->get('/tag/umkm')->assertOk()->assertSee('Tentang UMKM')->assertDontSee('Tidak Terkait');
    $this->get('/penulis/sari-wulandari')->assertOk()->assertSee('Sari Wulandari')->assertSee('Tentang UMKM');
    $this->get('/kategori/tidak-ada')->assertNotFound();
});

it('penulis tanpa artikel terbit dan penulis nonaktif tidak ditampilkan', function () {
    $kosong = User::factory()->create(['slug' => 'kosong']);
    $nonaktif = User::factory()->create(['slug' => 'nonaktif', 'is_active' => false]);
    Artikel::factory()->terbit()->create(['user_id' => $nonaktif->id]);

    $this->get('/penulis/kosong')->assertNotFound();
    $this->get('/penulis/nonaktif')->assertNotFound();
});

it('pencarian menemukan judul/isi, mengabaikan draf, dan noindex', function () {
    Artikel::factory()->terbit()->create(['judul' => 'Belajar Laravel', 'isi' => 'isi biasa']);
    Artikel::factory()->terbit()->create(['judul' => 'Resep', 'isi' => 'tips laravel di dalam isi']);
    Artikel::factory()->create(['judul' => 'Laravel Draf']);

    $this->get('/cari?q=laravel')->assertOk()->assertSee('Belajar Laravel')->assertSee('Resep')->assertDontSee('Laravel Draf')
        ->assertSee('noindex', false);
    $this->get('/cari?q=x')->assertOk()->assertSee('minimal 2 karakter');
    $this->get('/cari')->assertOk();
});

it('pencarian aman dari karakter wildcard dan injeksi', function () {
    Artikel::factory()->terbit()->create(['judul' => 'Normal']);

    $this->get('/cari?q=%25')->assertOk()->assertDontSee('Normal');
    $this->get("/cari?q=' OR 1=1 --")->assertOk()->assertDontSee('Normal');
});

it('meta SEO dasar tersedia: title, description, canonical, OG', function () {
    Artikel::factory()->terbit()->create(['judul' => 'Judul SEO', 'slug' => 'judul-seo', 'ringkasan' => 'Deskripsi singkat artikel.']);

    $this->get('/artikel/judul-seo')->assertSee('<title>Judul SEO — ', false)
        ->assertSee('<meta name="description" content="Deskripsi singkat artikel.">', false)
        ->assertSee('<link rel="canonical" href="'.url('/artikel/judul-seo').'">', false)
        ->assertSee('og:type" content="article"', false);
});

it('path artikel kustom: URL baru berfungsi, path lama dialihkan 301', function () {
    Artikel::factory()->terbit()->create(['slug' => 'contoh']);
    Settings::set('artikel_path', 'blog');
    Settings::set('artikel_path_lama', json_encode(['artikel']));

    muatUlangRute();

    $this->get('/blog/contoh')->assertOk();
    $this->get('/artikel/contoh')->assertStatus(301)->assertRedirect(url('/blog/contoh'));
    $this->get('/contoh')->assertStatus(301)->assertRedirect(url('/blog/contoh'));
});

it('path artikel kosong: artikel di /{slug}', function () {
    Artikel::factory()->terbit()->create(['slug' => 'langsung']);
    Settings::set('artikel_path', '');
    Settings::set('artikel_path_lama', json_encode(['artikel']));

    muatUlangRute();

    $this->get('/langsung')->assertOk();
    $this->get('/artikel/langsung')->assertStatus(301)->assertRedirect(url('/langsung'));
});

it('path admin kustom memindahkan panel', function () {
    Settings::set('admin_path', 'ruang-redaksi');

    muatUlangRute();

    $this->get('/ruang-redaksi/masuk')->assertOk();
    $this->get('/admin/masuk')->assertNotFound();
});

it('XSS di judul ter-escape', function () {
    Artikel::factory()->terbit()->create(['judul' => '<script>alert(1)</script>', 'slug' => 'xss']);

    $this->get('/')->assertDontSee('<script>alert(1)</script>', false);
});
