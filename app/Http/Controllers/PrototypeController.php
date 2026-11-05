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

    public function login(): View
    {
        return view('proto.admin.login', ['site' => Dummy::site()]);
    }

    public function adminDashboard(): View
    {
        $a = collect(Dummy::artikel());

        return $this->admin('proto.admin.dashboard', [
            'hitung' => ['Draf' => $a->where('status', 'draf')->count(), 'Terjadwal' => $a->where('status', 'terjadwal')->count(), 'Terbit' => $a->where('status', 'terbit')->count()],
            'tokenMinggu' => collect(Dummy::logAi())->sum('token'),
            'errorMinggu' => collect(Dummy::logAi())->where('status', 'error')->count(),
            'draf' => $a->where('status', 'draf'),
        ]);
    }

    public function adminArtikel(Request $request): View
    {
        $status = $request->query('status');
        $a = collect(Dummy::artikel())->when($status, fn ($c) => $c->where('status', $status));

        return $this->admin('proto.admin.artikel', ['artikel' => $a, 'status' => $status]);
    }

    public function adminEditor(int $id): View
    {
        $a = collect(Dummy::artikel())->firstWhere('id', $id) ?? abort(404);

        return $this->admin('proto.admin.editor', [
            'a' => $a,
            'saranLink' => collect(Dummy::artikel())->where('id', '!=', $id)->take(3),
            'cek' => [
                ['ok' => true, 'teks' => 'Judul 50–60 karakter'],
                ['ok' => true, 'teks' => 'Meta description 140–160 karakter'],
                ['ok' => false, 'teks' => 'Kata kunci utama muncul di 100 kata pertama'],
                ['ok' => true, 'teks' => 'Ada minimal 2 subjudul (H2)'],
                ['ok' => false, 'teks' => 'Minimal 2 link internal (baru 0)'],
                ['ok' => true, 'teks' => 'Semua gambar punya teks alt'],
                ['ok' => false, 'teks' => 'Panjang artikel ≥ 600 kata (baru 74)'],
            ],
        ]);
    }

    public function adminPengaturan(): View
    {
        return $this->admin('proto.admin.pengaturan');
    }

    public function adminTema(): View
    {
        return $this->admin('proto.admin.tema', ['tema' => Dummy::temaTerjadwal(), 'penulis' => Dummy::penulis()]);
    }

    public function adminLog(): View
    {
        return $this->admin('proto.admin.log', ['log' => Dummy::logAi()]);
    }

    private function admin(string $view, array $data = []): View
    {
        return view($view, $data + ['site' => Dummy::site(), 'kategori' => Dummy::kategori()]);
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
