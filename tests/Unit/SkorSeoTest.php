<?php

use App\Services\Seo\SkorSeo;

function seo(array $a = []): array
{
    $isiPanjang = "Panduan seo untuk blog dimulai dari riset.\n\n## Riset\n\n".str_repeat('kata biasa isi artikel ', 150).str_repeat('seo ', 8)."\n\n## Optimasi\n\nLihat [artikel lain](/artikel/lain) dan [satu lagi](/artikel/dua).";

    return (new SkorSeo)->analisis(...($a + [
        'judul' => 'Panduan SEO untuk Blog Baru Pemula 2026',
        'ringkasan' => str_repeat('a', 40).' seo '.str_repeat('b', 80),
        'kataKunci' => 'seo',
        'isi' => $isiPanjang,
        'gambar' => 'gambar/x.webp',
        'gambarAlt' => 'alt',
        'faq' => [['q' => 'a?', 'a' => 'b'], ['q' => 'c?', 'a' => 'd']],
        'hostSitus' => 'storika.id',
    ]));
}

it('memberi skor tinggi untuk artikel yang memenuhi aturan', function () {
    $r = seo();
    expect($r['skor'])->toBeGreaterThanOrEqual(90)
        ->and(collect($r['cek'])->every(fn ($c) => $c['ok']))->toBeTrue();
});

it('memberi skor rendah untuk artikel kosong', function () {
    $r = seo(['judul' => 'Pendek', 'ringkasan' => '', 'kataKunci' => '', 'isi' => '', 'gambar' => null, 'gambarAlt' => '', 'faq' => []]);
    expect($r['skor'])->toBeLessThan(30)->and($r['perlu_kata_kunci'])->toBeTrue();
});

it('melewati aturan kata kunci bila kata kunci kosong', function () {
    $dengan = count(seo()['cek']);
    $tanpa = count(seo(['kataKunci' => ''])['cek']);
    expect($tanpa)->toBeLessThan($dengan);
});

it('menghitung link internal unik, mengabaikan eksternal dan gambar', function () {
    $r = seo(['isi' => '[a](/x) [b](/x/) [c](https://storika.id/y) [d](https://lain.com/z) ![g](/img.png)', 'hostSitus' => 'storika.id']);
    $cek = collect($r['cek'])->first(fn ($c) => str_contains($c['teks'], 'link internal'));
    expect($cek['teks'])->toContain('sekarang 2');
});

it('mendeteksi gambar tanpa alt di isi', function () {
    $r = seo(['isi' => '![](https://x.test/a.png)']);
    expect(collect($r['cek'])->first(fn ($c) => str_contains($c['teks'], 'teks alt') && str_contains($c['teks'], 'isi'))['ok'])->toBeFalse();
});

it('mendeteksi kata kunci di 100 kata pertama dan judul', function () {
    $r = seo(['kataKunci' => 'kucing', 'isi' => str_repeat('anjing ', 120).' kucing']);
    $c = collect($r['cek']);
    expect($c->first(fn ($x) => str_contains($x['teks'], '100 kata'))['ok'])->toBeFalse()
        ->and($c->first(fn ($x) => str_contains($x['teks'], 'di judul'))['ok'])->toBeFalse();
});
