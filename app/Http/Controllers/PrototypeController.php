<?php

namespace App\Http\Controllers;

use App\Prototype\Dummy;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class PrototypeController extends Controller
{
    public function home(): View
    {
        $terbit = collect(Dummy::artikel())->where('status', 'terbit')->values();

        return $this->tampil('proto.home', ['utama' => $terbit->first(), 'terbaru' => $terbit->slice(1)]);
    }

    public function artikel(string $slug): View
    {
        $a = collect(Dummy::artikel())->firstWhere('slug', $slug) ?? abort(404);

        return $this->tampil('proto.artikel', [
            'a' => $a,
            'terkait' => collect(Dummy::artikel())->where('kategori', $a['kategori'])->where('id', '!=', $a['id'])->take(2),
            'penulisData' => collect(Dummy::penulis())->firstWhere('slug', $a['penulis']),
        ]);
    }

    public function kategori(string $slug): View
    {
        $k = collect(Dummy::kategori())->firstWhere('slug', $slug) ?? abort(404);

        return $this->tampil('proto.daftar', [
            'judul' => 'Kategori: '.$k['nama'],
            'artikel' => collect(Dummy::artikel())->where('kategori', $slug)->where('status', 'terbit'),
        ]);
    }

    public function tag(string $tag): View
    {
        return $this->tampil('proto.daftar', [
            'judul' => 'Tag: #'.$tag,
            'artikel' => collect(Dummy::artikel())->filter(fn ($a) => in_array($tag, $a['tags'], true) && $a['status'] === 'terbit'),
        ]);
    }

    public function penulis(string $slug): View
    {
        $p = collect(Dummy::penulis())->firstWhere('slug', $slug) ?? abort(404);

        return $this->tampil('proto.penulis', [
            'p' => $p,
            'artikel' => collect(Dummy::artikel())->where('penulis', $slug)->where('status', 'terbit'),
        ]);
    }

    public function cari(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $hasil = $q === '' ? collect() : collect(Dummy::artikel())->filter(fn ($a) => $a['status'] === 'terbit' && str_contains(strtolower($a['judul'].' '.$a['ringkasan']), strtolower($q)));

        return $this->tampil('proto.cari', ['q' => $q, 'hasil' => $hasil]);
    }

    private function tampil(string $view, array $data = []): View
    {
        return view($view, $data + [
            'site' => Dummy::site(),
            'kategori' => Dummy::kategori(),
            'semuaPenulis' => Dummy::penulis(),
            'semuaTags' => Dummy::tags(),
        ]);
    }
}
