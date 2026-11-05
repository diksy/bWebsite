<?php

use App\Models\User;
use App\Support\Settings;
use Illuminate\Support\Facades\DB;

it('menampilkan form masuk', function () {
    $this->get('/admin/masuk')->assertOk()->assertSee('Masuk');
});

it('mengizinkan pengguna aktif masuk', function () {
    $user = User::factory()->create(['password' => 'rahasia123', 'peran' => 'admin']);

    $this->post('/admin/masuk', ['email' => $user->email, 'password' => 'rahasia123'])
        ->assertRedirect('/admin');
    $this->assertAuthenticatedAs($user);
});

it('menolak kata sandi salah', function () {
    $user = User::factory()->create(['password' => 'rahasia123']);

    $this->post('/admin/masuk', ['email' => $user->email, 'password' => 'salah'])
        ->assertSessionHasErrors('email');
    $this->assertGuest();
});

it('menolak pengguna nonaktif', function () {
    $user = User::factory()->create(['password' => 'rahasia123', 'is_active' => false]);

    $this->post('/admin/masuk', ['email' => $user->email, 'password' => 'rahasia123'])
        ->assertSessionHasErrors('email');
});

it('memvalidasi input', function () {
    $this->post('/admin/masuk', [])->assertSessionHasErrors(['email', 'password']);
});

it('mengarahkan tamu ke halaman masuk', function () {
    $this->get('/admin')->assertRedirect('/admin/masuk');
});

it('menyimpan dan mengambil pengaturan, termasuk terenkripsi', function () {
    Settings::set('contoh', 'nilai');
    Settings::set('rahasia', 'sk-123', true);

    expect(Settings::get('contoh'))->toBe('nilai')
        ->and(Settings::get('rahasia'))->toBe('sk-123')
        ->and(DB::table('settings')->where('key', 'rahasia')->value('value'))->not->toBe('sk-123');
});
