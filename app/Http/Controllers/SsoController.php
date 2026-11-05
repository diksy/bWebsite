<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Sso\SsoClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Throwable;

class SsoController extends Controller
{
    public function mulai(Request $request, SsoClient $sso): RedirectResponse
    {
        if (! $sso->tersedia()) {
            return redirect()->route('admin.masuk')->withErrors(['email' => 'SSO belum aktif.']);
        }

        $a = $sso->mulai();
        $request->session()->put('sso', ['state' => $a['state'], 'nonce' => $a['nonce'], 'verifier' => $a['verifier']]);

        return redirect()->away($a['url']);
    }

    public function callback(Request $request, SsoClient $sso): RedirectResponse
    {
        $simpan = $request->session()->pull('sso');
        $gagal = fn (string $pesan) => redirect()->route('admin.masuk')->withErrors(['email' => $pesan]);

        if (! $sso->tersedia() || ! is_array($simpan)
            || ! hash_equals((string) $simpan['state'], (string) $request->query('state', ''))
            || ! $request->filled('code')) {
            return $gagal('Login SSO gagal atau kedaluwarsa. Coba lagi.');
        }

        try {
            ['claims' => $c, 'id_token' => $idToken] = $sso->selesai($request->string('code')->toString(), $simpan['verifier'], $simpan['nonce']);
        } catch (Throwable $e) {
            report($e);

            return $gagal('Login SSO gagal diverifikasi.');
        }

        $user = $this->cariPengguna((string) $c->sub, (string) ($c->email ?? ''));
        if ($user === null || ! $user->is_active) {
            return $gagal('Akun Anda belum didaftarkan di situs ini. Hubungi admin.');
        }

        if ($user->sso_subject === null) {
            $user->forceFill(['sso_subject' => (string) $c->sub])->save();
        }

        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->put('sso_id_token', $idToken);

        if (! empty($c->sid)) {
            DB::table('sso_sesi')->insert([
                'sid' => (string) $c->sid, 'session_id' => $request->session()->getId(), 'user_id' => $user->id,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        return redirect()->intended(route('admin.dasbor'));
    }

    public function backchannel(Request $request, SsoClient $sso): JsonResponse
    {
        try {
            $sid = $sso->verifikasiLogoutToken((string) $request->input('logout_token', ''));
        } catch (Throwable) {
            return response()->json(['error' => 'invalid_request'], 400);
        }

        $sesi = DB::table('sso_sesi')->where('sid', $sid)->pluck('session_id');
        DB::table('sessions')->whereIn('id', $sesi)->delete();
        DB::table('sso_sesi')->where('sid', $sid)->delete();

        return response()->json([], 204);
    }

    /** Pengguna harus sudah terdaftar: cocokkan sub, lalu email. */
    private function cariPengguna(string $sub, string $email): ?User
    {
        $user = User::query()->where('sso_subject', $sub)->first();
        if ($user === null && $email !== '') {
            $user = User::query()->where('email', $email)->whereNull('sso_subject')->first();
        }

        return $user;
    }
}
