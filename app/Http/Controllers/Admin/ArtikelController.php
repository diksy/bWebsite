<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Artikel;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ArtikelController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status');
        $cari = trim((string) $request->query('q', ''));
        $user = $request->user();

        $artikel = Artikel::query()
            ->with(['penulis:id,name', 'kategori:id,nama'])
            ->when(! $user->isAdmin(), fn ($q) => $q->where('user_id', $user->id))
            ->when(in_array($status, [Artikel::DRAF, Artikel::TERJADWAL, Artikel::TERBIT], true), fn ($q) => $q->where('status', $status))
            ->when($cari !== '', fn ($q) => $q->where('judul', 'like', '%'.addcslashes($cari, '%_\\').'%'))
            ->latest('updated_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.artikel.index', compact('artikel', 'status', 'cari'));
    }

    public function create(): View
    {
        return view('admin.artikel.editor', ['artikel' => null]);
    }

    public function edit(Artikel $artikel): View
    {
        Gate::authorize('kelola', $artikel);

        return view('admin.artikel.editor', compact('artikel'));
    }

    public function destroy(Artikel $artikel): RedirectResponse
    {
        Gate::authorize('kelola', $artikel);
        $artikel->delete();

        return redirect()->route('admin.artikel.index')->with('status', 'Artikel dihapus.');
    }
}
