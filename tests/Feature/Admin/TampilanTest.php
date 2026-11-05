<?php

use App\Models\GayaKustom;
use App\Models\User;
use App\Services\Gaya\Gaya;
use App\Services\Gaya\SanitasiCss;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    $this->admin = User::factory()->create(['peran' => 'admin']);
});

function cssUnggah(string $isi, string $nama = 'gaya.css'): UploadedFile
{
    return UploadedFile::fake()->createWithContent($nama, $isi);
}

it('memakai minimalis sebagai gaya default di situs publik', function () {
    expect(Gaya::aktif())->toBe('minimalis');
    $this->get('/')->assertOk()->assertSee('gaya/dasar.css', false)->assertSee('gaya/minimalis.css', false);
});

it('admin menerapkan gaya bawaan dan situs publik memuat CSS-nya', function () {
    $this->actingAs($this->admin)->put('/admin/tampilan', ['gaya' => 'neo-brutalism'])->assertRedirect();

    expect(Gaya::aktif())->toBe('neo-brutalism');
    $this->get('/')->assertSee('gaya/neo-brutalism.css', false)->assertDontSee('gaya/minimalis.css', false);
});

it('menolak menerapkan gaya yang tidak ada', function () {
    $this->actingAs($this->admin)->put('/admin/tampilan', ['gaya' => 'tidak-ada'])->assertSessionHasErrors('gaya');
    expect(Gaya::aktif())->toBe('minimalis');
});

it('mengunggah gaya kustom valid lalu menerapkannya', function () {
    $css = ":root{--g-bg:#fff;--g-fg:#111;--g-accent:#c2410c}\n.g-title{text-transform:uppercase}";
    $this->actingAs($this->admin)->post('/admin/tampilan/kustom', ['nama' => 'Senja Hangat', 'berkas' => cssUnggah($css)])
        ->assertSessionHasNoErrors()->assertRedirect();

    $g = GayaKustom::query()->where('slug', 'senja-hangat')->firstOrFail();
    Storage::disk('public')->assertExists($g->berkas);

    $this->actingAs($this->admin)->put('/admin/tampilan', ['gaya' => 'senja-hangat']);
    $this->get('/')->assertSee('senja-hangat.css', false);
});

it('menolak CSS berbahaya atau terlalu besar', function (string $isi) {
    $this->actingAs($this->admin)->post('/admin/tampilan/kustom', ['nama' => 'Buruk', 'berkas' => cssUnggah($isi)])
        ->assertSessionHasErrors('berkas');
    expect(GayaKustom::query()->count())->toBe(0);
})->with([
    '@import' => '@import "https://evil.example/x.css"; body{color:red}',
    'url eksternal' => 'body{background:url(https://evil.example/p.png)}',
    'expression' => 'body{width:expression(alert(1))}',
    'escape backslash' => '@\69mport "x"; body{color:red}',
    'tag html' => 'body{color:red}</style><script>alert(1)</script>',
    'javascript' => 'a{background:javascript:alert(1)}',
    'terlalu besar' => 'body{color:red}'.str_repeat('/* x */', 20000),
]);

it('menolak nama yang bentrok dengan gaya bawaan, nama kosong, dan bukan berkas css', function () {
    $this->actingAs($this->admin)->post('/admin/tampilan/kustom', ['nama' => 'Minimalis', 'berkas' => cssUnggah('a{color:red}')])->assertSessionHasErrors('slug');
    $this->actingAs($this->admin)->post('/admin/tampilan/kustom', ['nama' => '', 'berkas' => cssUnggah('a{color:red}')])->assertSessionHasErrors('nama');
    $this->actingAs($this->admin)->post('/admin/tampilan/kustom', ['nama' => 'Teks', 'berkas' => cssUnggah('a{color:red}', 'gaya.txt')])->assertSessionHasErrors('berkas');
});

it('menghapus gaya aktif mengembalikan situs ke minimalis dan menghapus berkasnya', function () {
    $this->actingAs($this->admin)->post('/admin/tampilan/kustom', ['nama' => 'Senja', 'berkas' => cssUnggah('a{color:red}')]);
    $g = GayaKustom::query()->firstOrFail();
    $this->actingAs($this->admin)->put('/admin/tampilan', ['gaya' => 'senja']);

    $this->actingAs($this->admin)->delete("/admin/tampilan/kustom/{$g->id}")->assertRedirect();

    Storage::disk('public')->assertMissing($g->berkas);
    expect(Gaya::aktif())->toBe('minimalis');
    $this->get('/')->assertSee('gaya/minimalis.css', false);
});

it('template contoh lolos sanitasi dan setiap gaya bawaan ada', function () {
    expect((new SanitasiCss)->periksa((string) file_get_contents(public_path('gaya/template-contoh.css'))))->toBe([]);
    foreach (array_keys(Gaya::BAWAAN) as $slug) {
        expect(public_path("gaya/{$slug}.css"))->toBeFile();
    }
    expect(public_path('gaya/dasar.css'))->toBeFile();
});

it('komentar tidak bisa dipakai menyembunyikan pelanggaran', function () {
    expect((new SanitasiCss)->periksa('/* ok */ @import "x"; a{color:red}'))->not->toBe([]);
    expect((new SanitasiCss)->periksa('/* @import "x" */ a{color:red}'))->toBe([]);
});

it('pratinjau tersedia untuk gaya yang ada dan 404 untuk lainnya', function () {
    $this->actingAs($this->admin)->get('/admin/tampilan/preview/glassmorphism')->assertOk()->assertSee('gaya/glassmorphism.css', false);
    $this->actingAs($this->admin)->get('/admin/tampilan/preview/minimalis/artikel')->assertOk();
    $this->actingAs($this->admin)->get('/admin/tampilan/preview/tidak-ada')->assertNotFound();
    $this->actingAs($this->admin)->get('/admin/tampilan')->assertOk()->assertSee('Neo Brutalism');
});

it('penulis tidak boleh mengakses pengaturan tampilan', function () {
    $penulis = User::factory()->create(['peran' => 'penulis']);
    $this->actingAs($penulis)->get('/admin/tampilan')->assertForbidden();
    $this->actingAs($penulis)->put('/admin/tampilan', ['gaya' => 'neo-brutalism'])->assertForbidden();
    $this->actingAs($penulis)->post('/admin/tampilan/kustom', ['nama' => 'X', 'berkas' => cssUnggah('a{color:red}')])->assertForbidden();
});
