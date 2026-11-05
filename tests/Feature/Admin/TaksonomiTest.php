<?php

use App\Models\Kategori;
use App\Models\Tag;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;

beforeEach(fn () => $this->admin = User::factory()->create(['peran' => 'admin']));

it('hanya admin yang boleh mengelola kategori & tag', function () {
    $this->actingAs(User::factory()->create())->get('/admin/kategori')->assertForbidden();
    $this->actingAs($this->admin)->get('/admin/kategori')->assertOk();
});

it('membuat kategori dengan slug otomatis', function () {
    $this->actingAs($this->admin)->post('/admin/kategori', ['nama' => 'Gaya Hidup Sehat'])->assertSessionHasNoErrors();
    expect(Kategori::first()->slug)->toBe('gaya-hidup-sehat');
});

it('menolak slug kategori ganda dan nama kosong', function () {
    Kategori::factory()->create(['nama' => 'Bisnis']);
    $this->actingAs($this->admin)->post('/admin/kategori', ['nama' => 'Bisnis'])->assertSessionHasErrors('slug');
    $this->actingAs($this->admin)->post('/admin/kategori', ['nama' => ''])->assertSessionHasErrors('nama');
});

it('memperbarui dan menghapus kategori', function () {
    $k = Kategori::factory()->create(['nama' => 'Lama']);
    $this->actingAs($this->admin)->put("/admin/kategori/{$k->id}", ['nama' => 'Baru', 'slug' => 'baru'])->assertSessionHasNoErrors();
    expect($k->fresh()->nama)->toBe('Baru');
    $this->actingAs($this->admin)->delete("/admin/kategori/{$k->id}");
    $this->assertModelMissing($k);
});

it('membuat dan menghapus tag, menolak duplikat', function () {
    $this->actingAs($this->admin)->post('/admin/tag', ['nama' => 'SEO'])->assertSessionHasNoErrors();
    expect(Tag::first()->slug)->toBe('seo');
    $this->actingAs($this->admin)->post('/admin/tag', ['nama' => 'seo'])->assertSessionHasErrors('slug');
    $this->actingAs($this->admin)->delete('/admin/tag/'.Tag::first()->id);
    expect(Tag::count())->toBe(0);
});

it('seeder membuat kategori awal dan penulis Redaksi tanpa kata sandi, idempoten', function () {
    $this->seed(DatabaseSeeder::class);
    $this->seed(DatabaseSeeder::class);

    expect(Kategori::count())->toBe(3)
        ->and(User::where('name', 'Redaksi')->count())->toBe(1)
        ->and(User::where('name', 'Redaksi')->first()->password)->toBeNull();
});
