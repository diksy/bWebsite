<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\MasukRequest;
use App\Services\Sso\SsoClient;
use App\Support\Settings;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MasukController extends Controller
{
    public function form(SsoClient $sso): View
    {
        return view('admin.masuk', [
            'ssoAktif' => $sso->tersedia(),
            'ssoWajib' => $this->ssoWajib($sso),
        ]);
    }

    /** Wajib SSO hanya berlaku bila SSO benar-benar terkonfigurasi (cegah terkunci). */
    private function ssoWajib(SsoClient $sso): bool
    {
        return Settings::get('sso_wajib') === '1' && $sso->tersedia();
    }

    public function masuk(MasukRequest $request, SsoClient $sso): RedirectResponse
    {
        if ($this->ssoWajib($sso)) {
            return back()->withErrors(['email' => 'Login kata sandi dinonaktifkan; gunakan SSO.']);
        }

        $berhasil = Auth::attempt(
            ['email' => $request->string('email')->toString(), 'password' => $request->string('password')->toString(), 'is_active' => true],
            $request->boolean('ingat'),
        );

        if (! $berhasil) {
            return back()->withErrors(['email' => 'Email atau kata sandi salah.'])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('admin.dasbor'));
    }

    public function keluar(Request $request, SsoClient $sso): RedirectResponse
    {
        $idToken = $request->session()->get('sso_id_token');
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if (is_string($idToken) && $sso->tersedia()) {
            return redirect()->away($sso->urlKeluar($idToken, route('admin.masuk')));
        }

        return redirect()->route('admin.masuk');
    }
}
