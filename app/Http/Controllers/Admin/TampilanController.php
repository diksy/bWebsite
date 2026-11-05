<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UnggahGayaRequest;
use App\Models\Artikel;
use App\Models\GayaKustom;
use App\Services\Gaya\Gaya;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TampilanController extends Controller
{
    public function index(): View
    {
        return view('admin.tampilan.index', [
            'bawaan' => Gaya::BAWAAN,
            'kustom' => GayaKustom::query()->orderBy('nama')->get(),
            'aktif' => Gaya::aktif(),
        ]);
    }

    public function terapkan(Request $request): RedirectResponse
    {
        $data = $request->validate(['gaya' => ['required', 'string', fn ($a, $v, $fail) => Gaya::ada((string) $v) ? null : $fail('Gaya tidak ditemukan.')]]);
        Gaya::setAktif($data['gaya']);

        return back()->with('status', 'Gaya situs diubah.');
    }

    public function unggah(UnggahGayaRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $isi = (string) file_get_contents($request->file('berkas')->getRealPath());
        $path = "gaya/{$data['slug']}.css";
        Storage::disk('public')->put($path, $isi);
        GayaKustom::query()->create(['nama' => $data['nama'], 'slug' => $data['slug'], 'berkas' => $path, 'ukuran' => strlen($isi)]);

        return back()->with('status', "Gaya “{$data['nama']}” diunggah. Pilih lalu terapkan untuk memakainya.");
    }

    public function hapus(GayaKustom $gaya): RedirectResponse
    {
        $gaya->delete();

        return back()->with('status', 'Gaya kustom dihapus.');
    }

    public function preview(string $gaya, string $halaman = 'beranda'): View
    {
        abort_unless(Gaya::ada($gaya), 404);
        abort_unless(in_array($halaman, ['beranda', 'artikel'], true), 404);

        $nyata = Artikel::query()->diterbitkan()->with(['kategori', 'penulis'])->latest('terbit_pada')->limit(3)->get();
        $contoh = $nyata->isNotEmpty() ? $nyata->map(fn (Artikel $a) => [
            'judul' => $a->judul, 'kategori' => $a->kategori->nama ?? 'Umum', 'penulis' => $a->penulis->name,
            'tanggal' => $a->terbit_pada->locale('id')->translatedFormat('d F Y'), 'ringkas' => $a->ringkasan,
        ])->all() : [
            ['judul' => 'Cara Memulai Blog yang Dibaca Orang', 'kategori' => 'Panduan', 'penulis' => 'Sari Dewi', 'tanggal' => '3 November 2026', 'ringkas' => 'Langkah praktis memilih topik, menulis konsisten, dan membangun pembaca pertama.'],
            ['judul' => 'SEO untuk Pemula: Mulai dari Mana?', 'kategori' => 'SEO', 'penulis' => 'Budi Santoso', 'tanggal' => '1 November 2026', 'ringkas' => 'Kata kunci, judul, dan tautan internal yang benar-benar berpengaruh.'],
            ['judul' => 'Menulis dengan AI Tanpa Kehilangan Suara Sendiri', 'kategori' => 'Teknologi', 'penulis' => 'Sari Dewi', 'tanggal' => '28 Oktober 2026', 'ringkas' => 'AI sebagai penyusun draf, editor manusia tetap penentu akhir.'],
        ];

        $daftarGaya = collect(Gaya::BAWAAN)->map(fn ($g) => $g['nama'])->all()
            + GayaKustom::query()->orderBy('nama')->pluck('nama', 'slug')->all();

        return view('admin.tampilan.preview', ['gaya' => $gaya, 'halaman' => $halaman, 'artikel' => $contoh, 'daftarGaya' => $daftarGaya, 'css' => Gaya::stylesheet($gaya)]);
    }
}
