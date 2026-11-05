<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SimpanSitusRequest;
use App\Services\Seo\IndexNow;
use App\Services\Sso\SsoClient;
use App\Support\Settings;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Throwable;

class PengaturanSitusController extends Controller
{
    public function edit(IndexNow $indexNow): View
    {
        return view('admin.situs', [
            'indexnow_aktif' => $indexNow->aktif(),
            'nama_situs' => Settings::get('nama_situs', config('app.name')),
            'tagline' => Settings::get('tagline', ''),
            'admin_path' => Settings::get('admin_path', 'admin'),
            'artikel_path' => Settings::get('artikel_path', 'artikel'),
            'sso' => [
                'aktif' => Settings::get('sso_aktif') === '1',
                'wajib' => Settings::get('sso_wajib') === '1',
                'base_url' => Settings::get('sso_base_url', ''),
                'client_id' => Settings::get('sso_client_id', ''),
                'ada_secret' => Settings::has('sso_client_secret'),
            ],
            'callback' => url('/sso/callback'),
            'backchannel' => url('/sso/backchannel-logout'),
            'postLogout' => route('admin.masuk'),
        ]);
    }

    public function update(SimpanSitusRequest $request): RedirectResponse
    {
        $d = $request->validated();

        $adminLama = Settings::get('admin_path', 'admin');
        $artikelLama = Settings::get('artikel_path', 'artikel');

        Settings::set('nama_situs', $d['nama_situs']);
        Settings::set('tagline', $d['tagline'] ?? '');
        Settings::set('indexnow_aktif', $d['indexnow_aktif'] ? '1' : '0');
        Settings::set('admin_path', $d['admin_path']);
        Settings::set('artikel_path', $d['artikel_path'] ?? '');
        if (($d['artikel_path'] ?? '') !== (string) $artikelLama) {
            // simpan path lama agar URL lama dialihkan 301 ke path baru
            $lama = json_decode((string) Settings::get('artikel_path_lama', '[]'), true) ?: [];
            $lama[] = (string) $artikelLama;
            Settings::set('artikel_path_lama', json_encode(array_values(array_unique($lama))));
        }

        Settings::set('sso_aktif', $d['sso_aktif'] ? '1' : '0');
        Settings::set('sso_wajib', $d['sso_wajib'] ? '1' : '0');
        Settings::set('sso_base_url', rtrim((string) ($d['sso_base_url'] ?? ''), '/'));
        Settings::set('sso_client_id', $d['sso_client_id'] ?? '');
        if (filled($d['sso_client_secret'] ?? null)) {
            Settings::set('sso_client_secret', $d['sso_client_secret'], true);
        }

        $this->segarkanRute();

        $pesan = 'Pengaturan disimpan.';
        if ($d['admin_path'] !== $adminLama) {
            return redirect('/'.$d['admin_path'].'/situs')->with('status', $pesan.' Panel admin kini di /'.$d['admin_path'].'.');
        }

        return redirect()->route('admin.pengaturan.situs')->with('status', $pesan);
    }

    public function ujiSso(SsoClient $sso): RedirectResponse
    {
        try {
            return back()->with('status', $sso->uji());
        } catch (Throwable $e) {
            return back()->withErrors(['sso_base_url' => 'Uji SSO gagal: '.$e->getMessage()]);
        }
    }

    /** Path berubah → cache rute (dibuat saat deploy/optimize) harus dibangun ulang. */
    private function segarkanRute(): void
    {
        if (app()->routesAreCached()) {
            Artisan::call('route:clear');
            Artisan::call('route:cache');
        }
    }
}
