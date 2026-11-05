<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Prototype\Dummy;
use Illuminate\Contracts\View\View;

/** Prototype layar "Tampilan" — data dummy, belum menyimpan apa pun. */
class PrototypeTampilanController extends Controller
{
    public function index(): View
    {
        return view('admin.tampilan.index', ['gaya' => Dummy::GAYA, 'kustom' => Dummy::KUSTOM, 'aktif' => 'minimalis']);
    }

    public function preview(string $gaya, string $halaman = 'beranda'): View
    {
        abort_unless(isset(Dummy::GAYA[$gaya]) || collect(Dummy::KUSTOM)->contains('slug', $gaya), 404);
        abort_unless(in_array($halaman, ['beranda', 'artikel'], true), 404);

        return view('admin.tampilan.preview', [
            'gaya' => $gaya, 'halaman' => $halaman, 'artikel' => Dummy::artikel(),
            'daftarGaya' => Dummy::GAYA + collect(Dummy::KUSTOM)->mapWithKeys(fn ($k) => [$k['slug'] => ['nama' => $k['nama']]])->all(),
        ]);
    }
}
