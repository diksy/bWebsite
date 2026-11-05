<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SimpanKategoriRequest;
use App\Models\Kategori;
use App\Models\Tag;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class KategoriController extends Controller
{
    public function index(): View
    {
        return view('admin.taksonomi', [
            'kategori' => Kategori::query()->orderBy('nama')->get(),
            'tag' => Tag::query()->orderBy('nama')->get(),
        ]);
    }

    public function store(SimpanKategoriRequest $request): RedirectResponse
    {
        Kategori::create($request->validated());

        return back()->with('status', 'Kategori dibuat.');
    }

    public function update(SimpanKategoriRequest $request, Kategori $kategori): RedirectResponse
    {
        $kategori->update($request->validated());

        return back()->with('status', 'Kategori diperbarui.');
    }

    public function destroy(Kategori $kategori): RedirectResponse
    {
        $kategori->delete();

        return back()->with('status', 'Kategori dihapus.');
    }
}
