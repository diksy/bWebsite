<?php

use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create(['peran' => 'admin']);
});

it('hanya admin yang boleh membuka daftar pengguna', function () {
    $penulis = User::factory()->create(['peran' => 'penulis']);

    $this->actingAs($penulis)->get('/admin/pengguna')->assertForbidden();
    $this->actingAs($this->admin)->get('/admin/pengguna')->assertOk()->assertSee($this->admin->email);
});

it('membuat pengguna baru', function () {
    $this->actingAs($this->admin)->post('/admin/pengguna', [
        'name' => 'Sari Wulandari', 'email' => 'sari@x.id', 'peran' => 'penulis', 'password' => 'rahasia123', 'is_active' => 1,
    ])->assertRedirect('/admin/pengguna');

    expect(User::where('email', 'sari@x.id')->first())->slug->toBe('sari-wulandari')->peran->toBe('penulis');
});

it('memvalidasi pembuatan pengguna', function () {
    $this->actingAs($this->admin)->post('/admin/pengguna', ['email' => $this->admin->email, 'peran' => 'dewa'])
        ->assertSessionHasErrors(['name', 'email', 'peran']);
});

it('memperbarui pengguna tanpa mengubah sandi bila kosong', function () {
    $u = User::factory()->create(['password' => 'lama12345']);
    $hash = $u->password;

    $this->actingAs($this->admin)->put("/admin/pengguna/{$u->id}", [
        'name' => 'Baru', 'email' => $u->email, 'peran' => 'penulis', 'password' => '', 'is_active' => 1,
    ])->assertRedirect('/admin/pengguna');

    expect($u->fresh()->name)->toBe('Baru')->and($u->fresh()->password)->toBe($hash);
});

it('tidak membolehkan admin terakhir diturunkan atau dihapus', function () {
    $this->actingAs($this->admin)->put("/admin/pengguna/{$this->admin->id}", [
        'name' => 'X', 'email' => $this->admin->email, 'peran' => 'penulis', 'is_active' => 1,
    ])->assertSessionHasErrors('peran');

    $lain = User::factory()->create(['peran' => 'admin']);
    $this->actingAs($lain)->delete("/admin/pengguna/{$this->admin->id}")->assertRedirect('/admin/pengguna');
    $this->actingAs($lain)->delete("/admin/pengguna/{$lain->id}")->assertSessionHasErrors('hapus');
});

it('menghapus pengguna', function () {
    $u = User::factory()->create();

    $this->actingAs($this->admin)->delete("/admin/pengguna/{$u->id}")->assertRedirect('/admin/pengguna');
    $this->assertModelMissing($u);
});
