<?php

use App\Models\User;
use App\Support\Settings;

beforeEach(fn () => $this->admin = User::factory()->create(['peran' => 'admin']));

function dataSitus(array $ganti = []): array
{
    return $ganti + ['nama_situs' => 'Storika', 'admin_path' => 'admin', 'artikel_path' => 'artikel'];
}

it('hanya admin yang boleh membuka pengaturan situs', function () {
    $this->actingAs(User::factory()->create())->get('/admin/situs')->assertForbidden();
    $this->actingAs($this->admin)->get('/admin/situs')->assertOk();
});

it('menyimpan path artikel baru dan mencatat path lama untuk redirect', function () {
    $this->actingAs($this->admin)->put('/admin/situs', dataSitus(['artikel_path' => 'blog']))->assertRedirect();

    expect(Settings::get('artikel_path'))->toBe('blog')
        ->and(json_decode(Settings::get('artikel_path_lama'), true))->toBe(['artikel']);
});

it('mengizinkan path artikel kosong', function () {
    $this->actingAs($this->admin)->put('/admin/situs', dataSitus(['artikel_path' => '']))->assertSessionHasNoErrors();
    expect(Settings::get('artikel_path'))->toBe('');
});

it('menolak path tidak valid, dicadangkan, atau bentrok', function (array $input, string $kolom) {
    $this->actingAs($this->admin)->put('/admin/situs', dataSitus($input))->assertSessionHasErrors($kolom);
})->with([
    'admin huruf besar/spasi' => [['admin_path' => 'Panel Saya'], 'admin_path'],
    'admin dicadangkan' => [['admin_path' => 'sitemap.xml'], 'admin_path'],
    'artikel dicadangkan' => [['artikel_path' => 'kategori'], 'artikel_path'],
    'artikel sama dengan admin' => [['admin_path' => 'panel', 'artikel_path' => 'panel'], 'artikel_path'],
    'sso aktif tanpa base url' => [['sso_aktif' => 1], 'sso_aktif'],
    'sso wajib tanpa aktif' => [['sso_wajib' => 1], 'sso_wajib'],
]);

it('menyimpan konfigurasi SSO dengan secret terenkripsi dan tidak menampilkannya', function () {
    $this->actingAs($this->admin)->put('/admin/situs', dataSitus([
        'sso_aktif' => 1, 'sso_base_url' => 'https://sso.contoh.test/', 'sso_client_id' => 'blog', 'sso_client_secret' => 'RAHASIA-XYZ',
    ]))->assertSessionHasNoErrors();

    expect(Settings::get('sso_base_url'))->toBe('https://sso.contoh.test')
        ->and(Settings::get('sso_client_secret'))->toBe('RAHASIA-XYZ');

    $this->actingAs($this->admin)->get('/admin/situs')->assertDontSee('RAHASIA-XYZ');
});
