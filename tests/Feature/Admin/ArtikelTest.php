<?php

use App\Livewire\Admin\ArtikelEditor;
use App\Models\Artikel;
use App\Models\Kategori;
use App\Models\Tag;
use App\Models\User;
use App\Support\Settings;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->create(['peran' => 'admin']);
    $this->penulis = User::factory()->create(['peran' => 'penulis']);
});

it('penulis hanya melihat artikelnya sendiri, admin melihat semua', function () {
    $milik = Artikel::factory()->create(['user_id' => $this->penulis->id, 'judul' => 'Artikel Milik Saya']);
    $lain = Artikel::factory()->create(['judul' => 'Artikel Orang Lain']);

    $this->actingAs($this->penulis)->get('/admin/artikel')->assertSee('Artikel Milik Saya')->assertDontSee('Artikel Orang Lain');
    $this->actingAs($this->admin)->get('/admin/artikel')->assertSee('Artikel Milik Saya')->assertSee('Artikel Orang Lain');
    $this->actingAs($this->penulis)->get("/admin/artikel/{$lain->id}/sunting")->assertForbidden();
    $this->actingAs($this->penulis)->delete("/admin/artikel/{$lain->id}")->assertForbidden();
    $this->actingAs($this->penulis)->get("/admin/artikel/{$milik->id}/sunting")->assertOk();
});

it('membuat draf dengan slug otomatis dan render markdown aman', function () {
    $kat = Kategori::factory()->create();
    $tag = Tag::factory()->create();

    Livewire::actingAs($this->penulis)->test(ArtikelEditor::class)
        ->set('judul', 'Panduan SEO untuk Pemula')
        ->set('isi', "## Halo\n\n<script>alert(1)</script>\n\n[x](javascript:alert(1))\n\n![gbr](https://x.test/a.png)")
        ->set('kategori_id', $kat->id)
        ->set('tagIds', [$tag->id])
        ->call('simpan')
        ->assertHasNoErrors();

    $a = Artikel::first();
    expect($a->slug)->toBe('panduan-seo-untuk-pemula')
        ->and($a->status)->toBe('draf')
        ->and($a->user_id)->toBe($this->penulis->id)
        ->and($a->tag)->toHaveCount(1)
        ->and($a->isi_html)->toContain('<h2>Halo</h2>')->not->toContain('<script>')->not->toContain('javascript:')
        ->and($a->isi_html)->toContain('loading="lazy"');
});

it('slug dibuat unik untuk judul kembar', function () {
    Artikel::factory()->create(['judul' => 'Sama', 'slug' => 'sama']);

    Livewire::actingAs($this->admin)->test(ArtikelEditor::class)->set('judul', 'Sama')->call('simpan')->assertHasNoErrors();

    expect(Artikel::pluck('slug')->all())->toContain('sama', 'sama-2');
});

it('memvalidasi judul, status, dan isi untuk artikel terbit', function () {
    Livewire::actingAs($this->admin)->test(ArtikelEditor::class)
        ->set('status', 'terbit')->call('simpan')
        ->assertHasErrors(['judul', 'isi']);

    Livewire::actingAs($this->admin)->test(ArtikelEditor::class)
        ->set('judul', 'X')->set('status', 'rahasia')->call('simpan')->assertHasErrors('status');
});

it('jadwal terbit wajib di masa depan', function () {
    $c = Livewire::actingAs($this->admin)->test(ArtikelEditor::class)
        ->set('judul', 'Terjadwal')->set('isi', 'Isi')->set('status', 'terjadwal');

    $c->set('terbit_pada', '')->call('simpan')->assertHasErrors('terbit_pada');
    $c->set('terbit_pada', now()->subDay()->format('Y-m-d\TH:i'))->call('simpan')->assertHasErrors('terbit_pada');
    $c->set('terbit_pada', now()->addDay()->format('Y-m-d\TH:i'))->call('simpan')->assertHasNoErrors();

    expect(Artikel::first())->status->toBe('terjadwal')->terbit_pada->not->toBeNull();
});

it('menerbitkan langsung mengisi tanggal terbit dan scope diterbitkan bekerja', function () {
    Livewire::actingAs($this->admin)->test(ArtikelEditor::class)
        ->set('judul', 'Langsung Terbit')->set('isi', 'Isi')->set('status', 'terbit')->call('simpan')->assertHasNoErrors();

    Artikel::factory()->create(['status' => 'terjadwal', 'terbit_pada' => now()->addDay()]);
    Artikel::factory()->create();

    expect(Artikel::diterbitkan()->count())->toBe(1);
});

it('penulis tidak bisa memindahkan kepemilikan artikel', function () {
    $a = Artikel::factory()->create(['user_id' => $this->penulis->id]);

    Livewire::actingAs($this->penulis)->test(ArtikelEditor::class, ['artikel' => $a])
        ->set('userId', $this->admin->id)->set('judul', 'Ganti')->call('simpan')->assertHasNoErrors();

    expect($a->fresh())->user_id->toBe($this->penulis->id)->judul->toBe('Ganti');
});

it('hanya FAQ lengkap yang disimpan', function () {
    Livewire::actingAs($this->admin)->test(ArtikelEditor::class)
        ->set('judul', 'FAQ')->set('faq', [['q' => 'Apa?', 'a' => 'Ini.'], ['q' => 'Kosong', 'a' => '']])->call('simpan');

    expect(Artikel::first()->faq)->toBe([['q' => 'Apa?', 'a' => 'Ini.']]);
});

it('url artikel mengikuti path artikel di pengaturan', function () {
    $a = Artikel::factory()->create(['slug' => 'contoh']);
    expect($a->url())->toEndWith('/artikel/contoh');

    Settings::set('artikel_path', 'blog');
    expect($a->url())->toEndWith('/blog/contoh');

    Settings::set('artikel_path', '');
    expect($a->url())->toEndWith('/contoh')->not->toContain('/blog');
});

it('menghapus artikel', function () {
    $a = Artikel::factory()->create(['user_id' => $this->penulis->id]);
    $this->actingAs($this->penulis)->delete("/admin/artikel/{$a->id}")->assertRedirect('/admin/artikel');
    $this->assertModelMissing($a);
});

it('menghapus pengguna ber-artikel harus memindahkan artikel', function () {
    Artikel::factory()->create(['user_id' => $this->penulis->id]);

    $this->actingAs($this->admin)->delete("/admin/pengguna/{$this->penulis->id}")->assertSessionHasErrors('hapus');
    $this->actingAs($this->admin)->delete("/admin/pengguna/{$this->penulis->id}", ['pindah_ke' => $this->admin->id])->assertRedirect('/admin/pengguna');

    expect(Artikel::first()->user_id)->toBe($this->admin->id);
});
