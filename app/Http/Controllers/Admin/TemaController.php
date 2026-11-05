<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SimpanTemaRequest;
use App\Models\Kategori;
use App\Models\Tema;
use App\Models\User;
use App\Services\Ai\Pipeline\Pipeline;
use App\Services\Ai\Pipeline\PipelineException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class TemaController extends Controller
{
    public function index(): View
    {
        return view('admin.tema.index', ['tema' => Tema::query()->with(['kategori:id,nama', 'penulis:id,name'])->orderByDesc('aktif')->orderBy('nama')->get()]);
    }

    public function create(): View
    {
        return view('admin.tema.form', $this->opsi(new Tema(['aktif' => true, 'frekuensi' => 'mingguan', 'batas_per_minggu' => 3])));
    }

    public function store(SimpanTemaRequest $request): RedirectResponse
    {
        Tema::create($request->validated());

        return redirect()->route('admin.tema.index')->with('status', 'Tema dibuat.');
    }

    public function edit(Tema $tema): View
    {
        return view('admin.tema.form', $this->opsi($tema));
    }

    public function update(SimpanTemaRequest $request, Tema $tema): RedirectResponse
    {
        $tema->update($request->validated());

        return redirect()->route('admin.tema.index')->with('status', 'Tema diperbarui.');
    }

    public function destroy(Tema $tema): RedirectResponse
    {
        $tema->delete(); // artikel hasil tema tetap ada (tema_id → null)

        return redirect()->route('admin.tema.index')->with('status', 'Tema dihapus.');
    }

    /** Jalankan pipeline sekarang (tetap menghormati batas draf/minggu). */
    public function jalankan(Tema $tema, Pipeline $pipeline): RedirectResponse
    {
        try {
            $pipeline->mulai($tema);
        } catch (PipelineException $e) {
            return back()->withErrors(['jalankan' => $e->getMessage()]);
        }

        return back()->with('status', 'Pipeline dimulai. Draf akan muncul di Artikel setelah antrean diproses (beberapa menit).');
    }

    /** @return array<string, mixed> */
    private function opsi(Tema $tema): array
    {
        return [
            't' => $tema,
            'daftarKategori' => Kategori::query()->orderBy('nama')->get(['id', 'nama']),
            'daftarPenulis' => User::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ];
    }
}
