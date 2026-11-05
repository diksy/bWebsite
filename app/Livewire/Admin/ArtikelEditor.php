<?php

namespace App\Livewire\Admin;

use App\Models\Artikel;
use App\Models\Kategori;
use App\Models\Tag;
use App\Models\User;
use App\Services\Gambar\Pengonversi;
use App\Services\Seo\SaranLinkInternal;
use App\Services\Seo\SkorSeo;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Throwable;

class ArtikelEditor extends Component
{
    use WithFileUploads;

    public ?int $artikelId = null;

    public int $userId = 0;

    public string $judul = '';

    public string $slug = '';

    public string $ringkasan = '';

    public string $kata_kunci = '';

    public string $isi = '';

    public ?int $kategori_id = null;

    /** @var array<int, int> */
    public array $tagIds = [];

    public string $status = Artikel::DRAF;

    public string $terbit_pada = '';

    /** @var array<int, array{q: string, a: string}> */
    public array $faq = [];

    /** @var array<int, array{judul?: string, url: string}> */
    public array $sumber = [];

    public bool $dibuatAi = false;

    public ?string $gambar = null;

    public string $gambar_alt = '';

    public ?TemporaryUploadedFile $gambarBaru = null;

    public ?TemporaryUploadedFile $gambarSisip = null;

    public function mount(?Artikel $artikel = null): void
    {
        $user = auth()->user();
        $this->userId = $user->id;

        if ($artikel?->exists) {
            Gate::authorize('kelola', $artikel);
            $this->artikelId = $artikel->id;
            $this->userId = $artikel->user_id;
            $this->judul = $artikel->judul;
            $this->slug = $artikel->slug;
            $this->ringkasan = (string) $artikel->ringkasan;
            $this->kata_kunci = (string) $artikel->kata_kunci;
            $this->isi = (string) $artikel->isi;
            $this->kategori_id = $artikel->kategori_id;
            $this->tagIds = $artikel->tag()->pluck('tag.id')->all();
            $this->status = $artikel->status;
            $this->terbit_pada = $artikel->terbit_pada?->timezone(config('app.timezone'))->format('Y-m-d\TH:i') ?? '';
            $this->faq = $artikel->faq ?? [];
            $this->sumber = $artikel->sumber ?? [];
            $this->dibuatAi = $artikel->dibuat_ai;
            $this->gambar = $artikel->gambar;
            $this->gambar_alt = (string) $artikel->gambar_alt;
        }
    }

    /** @return array<string, array<int, mixed>> */
    protected function rules(): array
    {
        $bukanDraf = $this->status !== Artikel::DRAF;

        return [
            'judul' => ['required', 'string', 'max:160'],
            'slug' => ['nullable', 'regex:/^[a-z0-9]+(-[a-z0-9]+)*$/', 'max:190', Rule::unique('artikel', 'slug')->ignore($this->artikelId)],
            'ringkasan' => ['nullable', 'string', 'max:320'],
            'kata_kunci' => ['nullable', 'string', 'max:120'],
            'isi' => [$bukanDraf ? 'required' : 'nullable', 'string'],
            'kategori_id' => ['nullable', 'integer', 'exists:kategori,id'],
            'tagIds' => ['array'],
            'tagIds.*' => ['integer', 'exists:tag,id'],
            'status' => ['required', Rule::in([Artikel::DRAF, Artikel::TERJADWAL, Artikel::TERBIT])],
            'terbit_pada' => [$this->status === Artikel::TERJADWAL ? 'required' : 'nullable', 'date', $this->status === Artikel::TERJADWAL ? 'after:now' : 'nullable'],
            'faq' => ['array', 'max:10'],
            'faq.*.q' => ['nullable', 'string', 'max:200'],
            'faq.*.a' => ['nullable', 'string', 'max:1000'],
            'gambar_alt' => ['nullable', 'string', 'max:160'],
            'userId' => [Rule::exists('users', 'id')->where('is_active', true)],
        ];
    }

    /** @return array<string, string> */
    protected function messages(): array
    {
        return [
            'terbit_pada.after' => 'Jadwal terbit harus di masa depan.',
            'terbit_pada.required' => 'Isi tanggal jadwal terbit.',
            'isi.required' => 'Isi artikel wajib diisi sebelum dijadwalkan/diterbitkan.',
            'slug.regex' => 'Slug hanya huruf kecil, angka, dan tanda hubung.',
        ];
    }

    public function updatedGambarBaru(Pengonversi $konversi): void
    {
        $path = $this->prosesUnggahan($this->gambarBaru, $konversi, 'gambarBaru');
        $this->gambarBaru = null;
        if ($path !== null) {
            $this->gambar = $path;
        }
    }

    public function updatedGambarSisip(Pengonversi $konversi): void
    {
        $path = $this->prosesUnggahan($this->gambarSisip, $konversi, 'gambarSisip');
        $this->gambarSisip = null;
        if ($path !== null) {
            $this->isi = rtrim($this->isi)."\n\n![".($this->judul ?: 'gambar').']('.Storage::disk('public')->url($path).')';
        }
    }

    public function hapusGambar(): void
    {
        $this->gambar = null;
        $this->gambar_alt = '';
    }

    private function prosesUnggahan(?TemporaryUploadedFile $file, Pengonversi $konversi, string $kolom): ?string
    {
        if ($file === null) {
            return null;
        }

        $this->validateOnly($kolom, [$kolom => ['image', 'mimes:jpg,jpeg,png,gif,webp', 'max:5120']], [
            "{$kolom}.image" => 'File harus berupa gambar.',
            "{$kolom}.mimes" => 'Format gambar: JPG, PNG, GIF, atau WebP.',
            "{$kolom}.max" => 'Ukuran gambar maksimal 5 MB.',
        ]);

        try {
            return $konversi->simpan((string) file_get_contents($file->getRealPath()));
        } catch (Throwable $e) {
            $this->addError($kolom, $e->getMessage());

            return null;
        } finally {
            $file->delete();
        }
    }

    /** Sisipkan link internal (path relatif) ke akhir isi. */
    public function sisipkanLink(int $id): void
    {
        $a = Artikel::query()->diterbitkan()->find($id);
        if ($a === null) {
            return;
        }

        $path = parse_url($a->url(), PHP_URL_PATH) ?: '/'.$a->slug;
        $this->isi = rtrim($this->isi)."\n\nBaca juga: [{$a->judul}]({$path})";
    }

    public function tambahFaq(): void
    {
        if (count($this->faq) < 10) {
            $this->faq[] = ['q' => '', 'a' => ''];
        }
    }

    public function hapusFaq(int $i): void
    {
        unset($this->faq[$i]);
        $this->faq = array_values($this->faq);
    }

    public function simpan(): mixed
    {
        $this->slug = Str::slug($this->slug);
        $this->validate();

        if (! auth()->user()->isAdmin()) {
            $this->userId = $this->artikelId ? Artikel::findOrFail($this->artikelId)->user_id : auth()->id();
        }

        $artikel = $this->artikelId ? Artikel::findOrFail($this->artikelId) : new Artikel;
        $gambarLama = $artikel->gambar;
        Gate::authorize('kelola', $artikel->exists ? $artikel : new Artikel(['user_id' => auth()->id()]));

        $terbitPada = match ($this->status) {
            Artikel::TERBIT => $this->terbit_pada !== '' ? Carbon::parse($this->terbit_pada) : ($artikel->terbit_pada ?? now()),
            Artikel::TERJADWAL => Carbon::parse($this->terbit_pada),
            default => $this->terbit_pada !== '' ? Carbon::parse($this->terbit_pada) : null,
        };

        DB::transaction(function () use ($artikel, $terbitPada): void {
            $artikel->fill([
                'user_id' => $this->userId,
                'judul' => $this->judul,
                'slug' => $this->slug,
                'ringkasan' => $this->ringkasan ?: null,
                'kata_kunci' => $this->kata_kunci ?: null,
                'isi' => $this->isi,
                'kategori_id' => $this->kategori_id,
                'gambar' => $this->gambar,
                'gambar_alt' => $this->gambar_alt ?: null,
                'status' => $this->status,
                'terbit_pada' => $terbitPada,
                'faq' => collect($this->faq)->filter(fn ($f) => filled($f['q']) && filled($f['a']))->values()->all(),
            ])->save();
            $artikel->tag()->sync($this->tagIds);
        });

        if ($gambarLama && $gambarLama !== $this->gambar) {
            Storage::disk('public')->delete($gambarLama);
        }

        session()->flash('status', 'Artikel disimpan.');

        if ($this->artikelId === null) {
            return $this->redirectRoute('admin.artikel.edit', $artikel, navigate: false);
        }

        $this->slug = $artikel->slug;
        $this->artikelId = $artikel->id;

        return null;
    }

    public function render(SkorSeo $seo, SaranLinkInternal $saran): View
    {
        return view('livewire.admin.artikel-editor', [
            'seo' => $seo->analisis($this->judul, $this->ringkasan, $this->kata_kunci, $this->isi, $this->gambar, $this->gambar_alt, $this->faq),
            'saranLink' => $saran->untuk($this->artikelId, $this->judul, $this->kata_kunci, $this->isi, $this->kategori_id),
            'daftarKategori' => Kategori::query()->orderBy('nama')->get(['id', 'nama']),
            'daftarTag' => Tag::query()->orderBy('nama')->get(['id', 'nama']),
            'daftarPenulis' => auth()->user()->isAdmin() ? User::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']) : collect(),
            'ringkasAi' => $this->dibuatAi,
        ]);
    }
}
