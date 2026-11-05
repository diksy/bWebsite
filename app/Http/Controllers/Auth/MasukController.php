<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\MasukRequest;
use App\Support\Settings;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MasukController extends Controller
{
    public function form(): View
    {
        return view('admin.masuk', [
            'ssoAktif' => Settings::get('sso_aktif') === '1',
            'ssoWajib' => Settings::get('sso_wajib') === '1',
        ]);
    }

    public function masuk(MasukRequest $request): RedirectResponse
    {
        if (Settings::get('sso_wajib') === '1') {
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

    public function keluar(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.masuk');
    }
}
