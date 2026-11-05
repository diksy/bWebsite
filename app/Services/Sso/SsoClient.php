<?php

namespace App\Services\Sso;

use App\Support\Settings;
use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Klien OIDC untuk BikininSSO: Authorization Code + PKCE (S256), verifikasi JWT via JWKS (RS256).
 */
class SsoClient
{
    public function baseUrl(): string
    {
        return rtrim((string) Settings::get('sso_base_url'), '/');
    }

    public function tersedia(): bool
    {
        return Settings::get('sso_aktif') === '1'
            && $this->baseUrl() !== ''
            && Settings::has('sso_client_id')
            && Settings::has('sso_client_secret');
    }

    public function redirectUri(): string
    {
        return url('/sso/callback');
    }

    /**
     * @return array{url: string, state: string, nonce: string, verifier: string}
     */
    public function mulai(): array
    {
        $verifier = $this->b64url(random_bytes(32));
        $state = $this->b64url(random_bytes(16));
        $nonce = $this->b64url(random_bytes(16));

        $url = $this->baseUrl().'/authorize?'.http_build_query([
            'response_type' => 'code',
            'client_id' => Settings::get('sso_client_id'),
            'redirect_uri' => $this->redirectUri(),
            'scope' => 'openid profile email',
            'state' => $state,
            'nonce' => $nonce,
            'code_challenge' => $this->b64url(hash('sha256', $verifier, true)),
            'code_challenge_method' => 'S256',
        ], '', '&', PHP_QUERY_RFC3986);

        return compact('url', 'state', 'nonce', 'verifier');
    }

    /**
     * Tukar code → token, verifikasi ID token, kembalikan klaim + id_token mentah.
     *
     * @return array{claims: object, id_token: string}
     */
    public function selesai(string $code, string $verifier, string $nonce): array
    {
        $resp = Http::asForm()->timeout(8)
            ->withBasicAuth((string) Settings::get('sso_client_id'), (string) Settings::get('sso_client_secret'))
            ->post($this->baseUrl().'/token', [
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => $this->redirectUri(),
                'code_verifier' => $verifier,
            ]);

        if (! $resp->successful() || ! is_string($resp->json('id_token'))) {
            throw new RuntimeException('Penukaran code di SSO gagal.');
        }

        $idToken = $resp->json('id_token');
        $claims = $this->verifikasi($idToken);

        if (! hash_equals($nonce, (string) ($claims->nonce ?? ''))) {
            throw new RuntimeException('Nonce tidak cocok.');
        }

        return ['claims' => $claims, 'id_token' => $idToken];
    }

    /**
     * Verifikasi tanda tangan (RS256 saja), iss, dan aud.
     */
    public function verifikasi(string $jwt): object
    {
        JWT::$leeway = 60;

        try {
            $claims = JWT::decode($jwt, JWK::parseKeySet($this->jwks(), 'RS256'));
        } catch (\Throwable) {
            // kid mungkin baru (rotasi kunci): ambil ulang JWKS sekali
            $claims = JWT::decode($jwt, JWK::parseKeySet($this->jwks(true), 'RS256'));
        }

        if (($claims->iss ?? null) !== $this->baseUrl()) {
            throw new RuntimeException('iss tidak cocok.');
        }
        if (! in_array(Settings::get('sso_client_id'), (array) ($claims->aud ?? []), true)) {
            throw new RuntimeException('aud tidak cocok.');
        }

        return $claims;
    }

    /**
     * Verifikasi logout token back-channel; kembalikan sid.
     */
    public function verifikasiLogoutToken(string $jwt): string
    {
        $claims = $this->verifikasi($jwt);
        $event = 'http://schemas.openid.net/event/backchannel-logout';

        if (! isset($claims->events->$event) || isset($claims->nonce) || empty($claims->sid)) {
            throw new RuntimeException('Logout token tidak valid.');
        }

        return (string) $claims->sid;
    }

    public function urlKeluar(string $idToken, string $kembaliKe): string
    {
        return $this->baseUrl().'/logout?'.http_build_query([
            'id_token_hint' => $idToken,
            'post_logout_redirect_uri' => $kembaliKe,
        ], '', '&', PHP_QUERY_RFC3986);
    }

    /**
     * Uji koneksi: ambil discovery dan JWKS.
     */
    public function uji(): string
    {
        $disc = Http::timeout(8)->get($this->baseUrl().'/.well-known/openid-configuration');
        if (! $disc->successful() || $disc->json('issuer') !== $this->baseUrl()) {
            throw new RuntimeException('Discovery tidak terbaca atau issuer berbeda dengan Base URL.');
        }
        $jumlah = count($this->jwks(true)['keys'] ?? []);

        return "Discovery & JWKS terbaca ({$jumlah} kunci).";
    }

    /** @return array<string, mixed> */
    private function jwks(bool $segar = false): array
    {
        $kunci = 'sso_jwks_'.md5($this->baseUrl());
        $bolehSegar = $segar && Cache::add($kunci.'_lock', 1, 60); // maks. 1x/menit

        if ($bolehSegar || ! Cache::has($kunci)) {
            $resp = Http::timeout(8)->get($this->baseUrl().'/jwks');
            if (! $resp->successful()) {
                throw new RuntimeException('JWKS tidak dapat diambil.');
            }
            Cache::put($kunci, $resp->json(), 3600);
        }

        return Cache::get($kunci);
    }

    private function b64url(string $biner): string
    {
        return rtrim(strtr(base64_encode($biner), '+/', '-_'), '=');
    }
}
