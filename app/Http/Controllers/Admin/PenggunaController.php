<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SimpanPenggunaRequest;
use App\Models\Artikel;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PenggunaController extends Controller
{
    public function index(): View
    {
        return view('admin.pengguna.index', ['pengguna' => User::query()->withCount('artikel')->orderBy('name')->get()]);
    }

    public function create(): View
    {
        return view('admin.pengguna.form', ['u' => new User(['is_active' => true, 'peran' => User::PERAN_PENULIS])]);
    }

    public function store(SimpanPenggunaRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['password'] = $data['password'] ?: null;
        User::create($data);

        return redirect()->route('admin.pengguna.index')->with('status', 'Pengguna dibuat.');
    }

    public function edit(User $pengguna): View
    {
        return view('admin.pengguna.form', ['u' => $pengguna]);
    }

    public function update(SimpanPenggunaRequest $request, User $pengguna): RedirectResponse
    {
        $data = $request->validated();

        if ($this->menghilangkanAdminTerakhir($pengguna, $data['peran'], (bool) $data['is_active'])) {
            return back()->withErrors(['peran' => 'Admin terakhir tidak boleh diturunkan atau dinonaktifkan.'])->withInput();
        }

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }
        $pengguna->update($data);

        return redirect()->route('admin.pengguna.index')->with('status', 'Pengguna diperbarui.');
    }

    public function destroy(Request $request, User $pengguna): RedirectResponse
    {
        if ($pengguna->is(auth()->user())) {
            return back()->withErrors(['hapus' => 'Anda tidak dapat menghapus akun sendiri.']);
        }
        if ($this->menghilangkanAdminTerakhir($pengguna, User::PERAN_PENULIS, false)) {
            return back()->withErrors(['hapus' => 'Admin terakhir tidak boleh dihapus.']);
        }

        if ($pengguna->artikel()->exists()) {
            $tujuan = User::query()->whereKey($request->integer('pindah_ke'))->where('id', '!=', $pengguna->id)->where('is_active', true)->first();
            if ($tujuan === null) {
                return back()->withErrors(['hapus' => 'Pengguna ini punya artikel; pilih penulis tujuan untuk memindahkannya.']);
            }
            Artikel::query()->where('user_id', $pengguna->id)->update(['user_id' => $tujuan->id]);
        }

        $pengguna->delete();

        return redirect()->route('admin.pengguna.index')->with('status', 'Pengguna dihapus.');
    }

    private function menghilangkanAdminTerakhir(User $pengguna, string $peranBaru, bool $aktifBaru): bool
    {
        if (! $pengguna->isAdmin() || ! $pengguna->is_active) {
            return false;
        }
        if ($peranBaru === User::PERAN_ADMIN && $aktifBaru) {
            return false;
        }

        return User::query()->where('peran', User::PERAN_ADMIN)->where('is_active', true)->count() <= 1;
    }
}
