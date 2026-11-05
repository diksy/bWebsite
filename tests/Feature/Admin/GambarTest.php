<?php

use App\Livewire\Admin\ArtikelEditor;
use App\Models\Artikel;
use App\Models\User;
use App\Services\Gambar\Pengonversi;
use App\Services\Gambar\WebpPengonversi;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

const PNG_1PX = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';

function pngUpload(string $nama = 'foto.png'): UploadedFile
{
    return UploadedFile::fake()->createWithContent($nama, base64_decode(PNG_1PX));
}

beforeEach(function () {
    Storage::fake('public');
    $this->admin = User::factory()->create(['peran' => 'admin']);

    // Pengonversi palsu: menyimpan biner apa adanya sebagai .webp (GD tidak selalu ada di mesin dev).
    $this->app->bind(Pengonversi::class, fn () => new class implements Pengonversi
    {
        public function simpan(string $biner): string
        {
            $path = 'gambar/uji/'.md5($biner).'.webp';
            Storage::disk('public')->put($path, $biner);

            return $path;
        }
    });
});

it('mengunggah gambar unggulan dan menyimpannya di artikel', function () {
    Livewire::actingAs($this->admin)->test(ArtikelEditor::class)
        ->set('judul', 'Dengan Gambar')
        ->set('gambarBaru', pngUpload())
        ->assertHasNoErrors()
        ->set('gambar_alt', 'Gambar uji')
        ->call('simpan');

    $a = Artikel::first();
    expect($a->gambar)->toEndWith('.webp')->and($a->gambar_alt)->toBe('Gambar uji');
    Storage::disk('public')->assertExists($a->gambar);
});

it('menyisipkan gambar ke isi sebagai markdown', function () {
    $c = Livewire::actingAs($this->admin)->test(ArtikelEditor::class)
        ->set('judul', 'Sisip')->set('isi', 'Awal')->set('gambarSisip', pngUpload());

    expect($c->get('isi'))->toContain('Awal')->toContain('![Sisip](')->toContain('.webp');
});

it('menolak file yang bukan gambar', function () {
    Livewire::actingAs($this->admin)->test(ArtikelEditor::class)
        ->set('gambarBaru', UploadedFile::fake()->create('virus.php', 5, 'application/x-php'))
        ->assertHasErrors('gambarBaru');
});

it('menolak gambar lebih dari 5 MB', function () {
    Livewire::actingAs($this->admin)->test(ArtikelEditor::class)
        ->set('gambarBaru', UploadedFile::fake()->create('besar.png', 6000, 'image/png'))
        ->assertHasErrors('gambarBaru');
});

it('mengganti gambar menghapus file lama', function () {
    Storage::disk('public')->put('gambar/lama.webp', 'x');
    $a = Artikel::factory()->create(['gambar' => 'gambar/lama.webp']);

    Livewire::actingAs($this->admin)->test(ArtikelEditor::class, ['artikel' => $a])->call('hapusGambar')->call('simpan');

    Storage::disk('public')->assertMissing('gambar/lama.webp');
    expect($a->fresh()->gambar)->toBeNull();
});

it('konversi nyata ke WebP (butuh GD)', function () {
    $this->app->bind(Pengonversi::class, WebpPengonversi::class);

    $path = app(Pengonversi::class)->simpan(base64_decode(PNG_1PX));

    expect($path)->toEndWith('.webp');
    expect(substr(Storage::disk('public')->get($path), 8, 4))->toBe('WEBP');
})->skip(! function_exists('imagewebp'), 'Ekstensi GD/WebP tidak tersedia di mesin ini');

it('pengonversi menolak biner yang bukan gambar', function () {
    $this->app->bind(Pengonversi::class, WebpPengonversi::class);

    expect(fn () => app(Pengonversi::class)->simpan('<?php echo 1;'))->toThrow(RuntimeException::class);
});
