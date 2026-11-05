<?php

use App\Models\Artikel;
use App\Models\Kategori;
use App\Models\User;
use App\Support\Settings;

function jsonLd(string $html): array
{
    preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $html, $m);

    return array_map(fn ($j) => json_decode($j, true, flags: JSON_THROW_ON_ERROR), $m[1]);
}

it('artikel memuat JSON-LD Article, BreadcrumbList, FAQPage, dan Person', function () {
    $kat = Kategori::factory()->create(['nama' => 'Bisnis', 'slug' => 'bisnis']);
    $pen = User::factory()->create(['name' => 'Sari', 'slug' => 'sari']);
    Artikel::factory()->terbit()->create([
        'judul' => 'Judul Artikel', 'slug' => 'judul-artikel', 'user_id' => $pen->id, 'kategori_id' => $kat->id,
        'faq' => [['q' => 'Apa?', 'a' => 'Itu.']],
    ]);

    $ld = collect(jsonLd($this->get('/artikel/judul-artikel')->getContent()));
    $article = $ld->firstWhere('@type', 'Article');

    expect($article['headline'])->toBe('Judul Artikel')
        ->and($article['author']['@type'])->toBe('Person')
        ->and($article['author']['name'])->toBe('Sari')
        ->and($article['inLanguage'])->toBe('id-ID')
        ->and($article['mainEntityOfPage']['@id'])->toBe(url('/artikel/judul-artikel'))
        ->and($ld->firstWhere('@type', 'BreadcrumbList')['itemListElement'])->toHaveCount(3)
        ->and($ld->firstWhere('@type', 'FAQPage')['mainEntity'][0]['name'])->toBe('Apa?');
});

it('FAQPage tidak dibuat bila FAQ kosong', function () {
    Artikel::factory()->terbit()->create(['slug' => 'tanpa-faq', 'faq' => []]);

    $tipe = collect(jsonLd($this->get('/artikel/tanpa-faq')->getContent()))->pluck('@type');
    expect($tipe)->not->toContain('FAQPage');
});

it('halaman penulis memuat Person; beranda memuat WebSite', function () {
    $pen = User::factory()->create(['name' => 'Sari', 'slug' => 'sari', 'bio' => 'Bio']);
    Artikel::factory()->terbit()->create(['user_id' => $pen->id]);

    expect(collect(jsonLd($this->get('/penulis/sari')->getContent()))->firstWhere('@type', 'Person')['description'])->toBe('Bio');
    expect(collect(jsonLd($this->get('/')->getContent()))->firstWhere('@type', 'WebSite'))->not->toBeNull();
});

it('JSON-LD aman dari injeksi </script>', function () {
    Artikel::factory()->terbit()->create(['judul' => 'Judul </script><script>alert(1)</script>', 'slug' => 'inj']);

    $html = $this->get('/artikel/inj')->getContent();
    expect($html)->not->toContain('</script><script>alert(1)');
    expect(jsonLd($html))->not->toBeEmpty();
});

it('sitemap.xml memuat halaman terbit saja', function () {
    Artikel::factory()->terbit()->create(['slug' => 'terbit-a']);
    Artikel::factory()->create(['slug' => 'draf-b']);
    Artikel::factory()->create(['slug' => 'nanti-c', 'status' => 'terjadwal', 'terbit_pada' => now()->addDay()]);
    $kat = Kategori::factory()->create(['slug' => 'ada']);
    Kategori::factory()->create(['slug' => 'kosong']);
    Artikel::factory()->terbit()->create(['kategori_id' => $kat->id]);

    $r = $this->get('/sitemap.xml')->assertOk()->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
    $xml = simplexml_load_string($r->getContent());

    $path = [];
    foreach ($xml->url as $u) {
        $path[] = parse_url((string) $u->loc, PHP_URL_PATH) ?: '/';
    }
    expect($path)->toContain('/', '/artikel/terbit-a', '/kategori/ada')
        ->not->toContain('/artikel/draf-b', '/artikel/nanti-c', '/kategori/kosong');
});

it('sitemap mengikuti path artikel kustom', function () {
    Artikel::factory()->terbit()->create(['slug' => 'x']);
    Settings::set('artikel_path', 'blog');

    expect($this->get('/sitemap.xml')->getContent())->toContain('/blog/x');
});

it('RSS valid, berisi artikel terbit, dan aman terhadap CDATA', function () {
    Artikel::factory()->terbit()->create(['judul' => 'Dengan & Simbol', 'slug' => 'rss-a', 'isi' => 'Teks ]]> berbahaya']);
    Artikel::factory()->create(['judul' => 'Draf Tersembunyi']);

    $r = $this->get('/rss.xml')->assertOk();
    $xml = simplexml_load_string($r->getContent());

    expect((string) $xml->channel->item[0]->title)->toBe('Dengan & Simbol')
        ->and(count($xml->channel->item))->toBe(1)
        ->and($r->getContent())->not->toContain('Draf Tersembunyi');
});

it('robots.txt menutup panel admin dan menunjuk sitemap', function () {
    $this->get('/robots.txt')->assertOk()->assertSee('Disallow: /admin/')->assertSee('Sitemap: '.url('sitemap.xml'));

    Settings::set('admin_path', 'redaksi');
    $this->get('/robots.txt')->assertSee('Disallow: /redaksi/');
});

it('halaman publik memuat link RSS di head', function () {
    $this->get('/')->assertSee('application/rss+xml', false);
});
