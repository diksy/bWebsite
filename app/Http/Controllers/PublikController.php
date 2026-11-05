<?php

namespace App\Http\Controllers;

use App\Models\Artikel;
use App\Models\Kategori;
use App\Models\Tag;
use App\Models\User;
use App\Services\Seo\JsonLd;
use App\Support\Situs;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PublikController extends Controller
{
    private const PER_HALAMAN = 9;

    public function beranda(Request $request, JsonLd $ld): View
    {
        $daftar = $this->dasar()->latest('terbit_pada')->paginate(self::PER_HALAMAN)->withQueryString();
        $utama = $daftar->currentPage() === 1 ? $daftar->first() : null;

        return view('publik.beranda', [
            'utama' => $utama,
            'artikel' => $utama ? $daftar->getCollection()->slice(1) : $daftar->getCollection(),
            'daftar' => $daftar,
            'tagPopuler' => Tag::query()->withCount(['artikel' => fn ($q) => $q->diterbitkan()])->orderByDesc('artikel_count')->limit(12)->get()->where('artikel_count', '>', 0),
            'jsonld' => $ld->beranda(),
            'meta' => $this->meta(Situs::nama().(Situs::tagline() ? ' — '.Situs::tagline() : ''), Situs::tagline(), url('/')),
        ]);
    }

    public function artikel(string $slug, JsonLd $ld): View|RedirectResponse
    {
        $artikel = $this->dasar()->with(['tag', 'kategori', 'penulis'])->where('slug', $slug)->firstOrFail();

        $terkait = $this->dasar()
            ->where('id', '!=', $artikel->id)
            ->when($artikel->kategori_id, fn ($q) => $q->where('kategori_id', $artikel->kategori_id))
            ->latest('terbit_pada')->limit(3)->get();

        return view('publik.artikel', [
            'a' => $artikel,
            'terkait' => $terkait,
            'jsonld' => $ld->artikel($artikel),
            'meta' => $this->meta($artikel->judul.' — '.Situs::nama(), (string) ($artikel->ringkasan ?: str($artikel->isi)->limit(155)), $artikel->url(), 'article', $artikel->gambar),
        ]);
    }

    /** /{slug} saat path artikel diisi: alihkan permanen ke URL kanonik. */
    public function artikelTanpaPath(string $slug): RedirectResponse
    {
        $a = $this->dasar()->where('slug', $slug)->firstOrFail();

        return redirect()->to($a->url(), 301);
    }

    /** Path artikel lama (sebelum diganti di pengaturan) → URL baru. */
    public function artikelPathLama(string $slug): RedirectResponse
    {
        $a = $this->dasar()->where('slug', $slug)->firstOrFail();

        return redirect()->to($a->url(), 301);
    }

    public function kategori(string $slug, JsonLd $ld): View
    {
        $kategori = Kategori::query()->where('slug', $slug)->firstOrFail();
        $daftar = $this->dasar()->where('kategori_id', $kategori->id)->latest('terbit_pada')->paginate(self::PER_HALAMAN);

        return view('publik.daftar', [
            'judul' => $kategori->nama,
            'deskripsi' => $kategori->deskripsi,
            'jenis' => 'Kategori',
            'jsonld' => [$ld->breadcrumb([['Beranda', url('/')], [$kategori->nama, url('kategori/'.$kategori->slug)]])],
            'daftar' => $daftar,
            'meta' => $this->meta("{$kategori->nama} — ".Situs::nama(), $kategori->deskripsi ?: "Artikel kategori {$kategori->nama}", url('kategori/'.$kategori->slug)),
        ]);
    }

    public function tag(string $slug): View
    {
        $tag = Tag::query()->where('slug', $slug)->firstOrFail();
        $daftar = $this->dasar()->whereHas('tag', fn ($q) => $q->where('tag.id', $tag->id))->latest('terbit_pada')->paginate(self::PER_HALAMAN);

        return view('publik.daftar', [
            'judul' => '#'.$tag->nama,
            'deskripsi' => null,
            'jenis' => 'Tag',
            'daftar' => $daftar,
            'meta' => $this->meta("#{$tag->nama} — ".Situs::nama(), "Artikel dengan tag {$tag->nama}", url('tag/'.$tag->slug)),
        ]);
    }

    public function penulis(string $slug, JsonLd $ld): View
    {
        $penulis = User::query()->where('slug', $slug)->where('is_active', true)->firstOrFail();
        $daftar = $this->dasar()->where('user_id', $penulis->id)->latest('terbit_pada')->paginate(self::PER_HALAMAN);
        abort_if($daftar->total() === 0, 404); // penulis tanpa artikel terbit tidak ditampilkan

        return view('publik.penulis', [
            'p' => $penulis,
            'jsonld' => $ld->penulis($penulis),
            'daftar' => $daftar,
            'meta' => $this->meta("{$penulis->name} — ".Situs::nama(), $penulis->bio ?: "Artikel oleh {$penulis->name}", url('penulis/'.$penulis->slug), 'profile'),
        ]);
    }

    public function cari(Request $request): View
    {
        $q = mb_substr(trim((string) $request->query('q', '')), 0, 80);
        $hasil = null;

        if (mb_strlen($q) >= 2) {
            $like = '%'.addcslashes($q, '%_\\').'%';
            $hasil = $this->dasar()
                ->where(fn ($w) => $w->where('judul', 'like', $like)->orWhere('ringkasan', 'like', $like)->orWhere('isi', 'like', $like))
                ->orderByRaw('case when judul like ? then 0 else 1 end', [$like])
                ->latest('terbit_pada')
                ->paginate(10)->withQueryString();
        }

        return view('publik.cari', [
            'q' => $q,
            'hasil' => $hasil,
            'meta' => $this->meta('Cari'.($q !== '' ? ": {$q}" : '').' — '.Situs::nama(), 'Pencarian artikel', url('cari'), 'website', null, 'noindex,follow'),
        ]);
    }

    /** @return Builder<Artikel> */
    private function dasar()
    {
        return Artikel::query()->diterbitkan()->with(['penulis:id,name,slug', 'kategori:id,nama,slug']);
    }

    /** @return array<string, ?string> */
    private function meta(string $judul, string $deskripsi, string $kanonik, string $tipe = 'website', ?string $gambar = null, string $robots = 'index,follow'): array
    {
        return [
            'judul' => $judul,
            'deskripsi' => mb_substr(trim(preg_replace('/\s+/u', ' ', strip_tags($deskripsi)) ?? ''), 0, 200),
            'kanonik' => $kanonik,
            'tipe' => $tipe,
            'gambar' => $gambar ? Storage::disk('public')->url($gambar) : null,
            'robots' => $robots,
        ];
    }
}
