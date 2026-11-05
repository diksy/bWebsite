<?php

use App\Models\Artikel;
use App\Models\Kategori;
use App\Models\Tema;
use App\Models\User;

beforeEach(fn () => $this->admin = User::factory()->create(['peran' => 'admin']));

function dataTema(array $ganti = []): array
{
    return $ganti + [
        'nama' => 'Tips SEO UMKM', 'kata_kunci' => 'seo umkm, blog bisnis', 'frekuensi' => 'harian',
        'user_id' => User::factory()->create()->id, 'batas_per_minggu' => 3, 'aktif' => 1,
    ];
}

it('hanya admin yang boleh mengelola tema', function () {
    $this->actingAs(User::factory()->create())->get('/admin/tema')->assertForbidden();
    $this->actingAs($this->admin)->get('/admin/tema')->assertOk();
});

it('membuat, memperbarui, dan menghapus tema', function () {
    $kat = Kategori::factory()->create();
    $this->actingAs($this->admin)->post('/admin/tema', dataTema(['kategori_id' => $kat->id]))->assertRedirect('/admin/tema');
    $t = Tema::first();
    expect($t->nama)->toBe('Tips SEO UMKM')->and($t->aktif)->toBeTrue();

    $this->actingAs($this->admin)->put("/admin/tema/{$t->id}", dataTema(['nama' => 'Baru', 'aktif' => 0, 'user_id' => $t->user_id]))->assertRedirect('/admin/tema');
    expect($t->fresh()->nama)->toBe('Baru')->and($t->fresh()->aktif)->toBeFalse();

    $this->actingAs($this->admin)->delete("/admin/tema/{$t->id}");
    $this->assertModelMissing($t);
});

it('memvalidasi input tema', function (array $input, string $kolom) {
    $this->actingAs($this->admin)->post('/admin/tema', dataTema($input))->assertSessionHasErrors($kolom);
})->with([
    'nama kosong' => [['nama' => ''], 'nama'],
    'kata kunci kosong' => [['kata_kunci' => ''], 'kata_kunci'],
    'frekuensi asing' => [['frekuensi' => 'tiap-detik'], 'frekuensi'],
    'batas nol' => [['batas_per_minggu' => 0], 'batas_per_minggu'],
    'batas terlalu besar' => [['batas_per_minggu' => 99], 'batas_per_minggu'],
    'penulis tidak ada' => [['user_id' => 9999], 'user_id'],
    'kategori tidak ada' => [['kategori_id' => 9999], 'kategori_id'],
]);

it('menolak penulis nonaktif', function () {
    $mati = User::factory()->create(['is_active' => false]);
    $this->actingAs($this->admin)->post('/admin/tema', dataTema(['user_id' => $mati->id]))->assertSessionHasErrors('user_id');
});

it('jatuh tempo mengikuti frekuensi dan status aktif', function () {
    $baru = Tema::factory()->create(['frekuensi' => 'mingguan', 'terakhir_jalan' => null]);
    $belum = Tema::factory()->create(['frekuensi' => 'mingguan', 'terakhir_jalan' => now()->subDays(3)]);
    $sudah = Tema::factory()->create(['frekuensi' => 'mingguan', 'terakhir_jalan' => now()->subDays(8)]);
    $jeda = Tema::factory()->create(['aktif' => false, 'terakhir_jalan' => null]);

    expect($baru->jatuhTempo())->toBeTrue()
        ->and($belum->jatuhTempo())->toBeFalse()
        ->and($sudah->jatuhTempo())->toBeTrue()
        ->and($jeda->jatuhTempo())->toBeFalse();
});

it('kuota mingguan menghitung hanya draf AI minggu ini dari tema itu', function () {
    $t = Tema::factory()->create(['batas_per_minggu' => 2, 'terakhir_jalan' => null]);
    Artikel::factory()->create(['tema_id' => $t->id, 'dibuat_ai' => true]);
    Artikel::factory()->create(['tema_id' => $t->id, 'dibuat_ai' => false]);                                       // manual: tidak dihitung
    Artikel::factory()->create(['tema_id' => $t->id, 'dibuat_ai' => true, 'created_at' => now()->subWeeks(2)]);     // minggu lalu
    Artikel::factory()->create(['dibuat_ai' => true]);                                                              // tema lain

    expect($t->draftMingguIni())->toBe(1)->and($t->kuotaTersisa())->toBe(1)->and($t->layakJalan())->toBeTrue();

    Artikel::factory()->create(['tema_id' => $t->id, 'dibuat_ai' => true]);
    expect($t->fresh()->kuotaTersisa())->toBe(0)->and($t->fresh()->layakJalan())->toBeFalse();
});

it('memecah kata kunci', function () {
    $t = new Tema(['kata_kunci' => "seo umkm, blog bisnis\n marketing ,, "]);
    expect($t->daftarKataKunci())->toBe(['seo umkm', 'blog bisnis', 'marketing']);
});

it('menghapus tema tidak menghapus artikelnya', function () {
    $t = Tema::factory()->create();
    $a = Artikel::factory()->create(['tema_id' => $t->id]);

    $this->actingAs($this->admin)->delete("/admin/tema/{$t->id}");

    expect($a->fresh())->not->toBeNull()->and($a->fresh()->tema_id)->toBeNull();
});

it('pengguna yang menjadi penulis tema tidak bisa dihapus', function () {
    $t = Tema::factory()->create();

    $this->actingAs($this->admin)->delete("/admin/pengguna/{$t->user_id}")->assertSessionHasErrors('hapus');
    $this->assertModelExists($t->penulis);
});
