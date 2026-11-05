<?php

use App\Models\User;
use App\Support\Settings;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

const SSO = 'https://sso.contoh.test';

function kunciSso(): array
{
    static $kunci = null;
    if ($kunci === null) {
        $res = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
        openssl_pkey_export($res, $pem);
        $d = openssl_pkey_get_details($res);
        $b = fn (string $x) => rtrim(strtr(base64_encode($x), '+/', '-_'), '=');
        $kunci = ['pem' => $pem, 'jwks' => ['keys' => [['kty' => 'RSA', 'kid' => 'k1', 'use' => 'sig', 'alg' => 'RS256', 'n' => $b($d['rsa']['n']), 'e' => $b($d['rsa']['e'])]]]];
    }

    return $kunci;
}

function jwtSso(array $klaim, string $kid = 'k1'): string
{
    return JWT::encode($klaim + ['iss' => SSO, 'aud' => 'blog', 'iat' => time(), 'exp' => time() + 600], kunciSso()['pem'], 'RS256', $kid);
}

beforeEach(function () {
    Settings::set('sso_aktif', '1');
    Settings::set('sso_base_url', SSO);
    Settings::set('sso_client_id', 'blog');
    Settings::set('sso_client_secret', 'rahasia', true);
    Cache::flush();
});

function mulaiSso($test): array
{
    $loc = $test->get('/sso/mulai')->assertRedirect()->headers->get('Location');
    parse_str(parse_url($loc, PHP_URL_QUERY), $q);

    return [$loc, $q];
}

it('mengarahkan ke SSO dengan PKCE S256', function () {
    [$loc, $q] = mulaiSso($this);

    expect($loc)->toStartWith(SSO.'/authorize?')
        ->and($q['code_challenge_method'])->toBe('S256')
        ->and($q['client_id'])->toBe('blog')
        ->and($q['redirect_uri'])->toEndWith('/sso/callback');
});

it('login lewat SSO untuk pengguna terdaftar dan mengikat sub', function () {
    $user = User::factory()->create(['email' => 'a@x.id', 'password' => null, 'peran' => 'admin']);
    [, $q] = mulaiSso($this);

    Http::fake([
        SSO.'/token' => Http::response(['id_token' => jwtSso(['sub' => 'uuid-1', 'email' => 'a@x.id', 'nonce' => $q['nonce'], 'sid' => 's1'])]),
        SSO.'/jwks' => Http::response(kunciSso()['jwks']),
    ]);

    $this->get('/sso/callback?code=abc&state='.$q['state'])->assertRedirect('/admin');
    $this->assertAuthenticatedAs($user);
    expect($user->fresh()->sso_subject)->toBe('uuid-1')
        ->and(DB::table('sso_sesi')->where('sid', 's1')->exists())->toBeTrue();
});

it('menolak email yang belum terdaftar (tanpa auto-buat akun)', function () {
    [, $q] = mulaiSso($this);
    Http::fake([
        SSO.'/token' => Http::response(['id_token' => jwtSso(['sub' => 'u2', 'email' => 'asing@x.id', 'nonce' => $q['nonce']])]),
        SSO.'/jwks' => Http::response(kunciSso()['jwks']),
    ]);

    $this->get('/sso/callback?code=abc&state='.$q['state'])->assertRedirect('/admin/masuk')->assertSessionHasErrors('email');
    $this->assertGuest();
    expect(User::where('email', 'asing@x.id')->exists())->toBeFalse();
});

it('menolak pengguna nonaktif, state salah, dan nonce salah', function () {
    User::factory()->create(['email' => 'n@x.id', 'is_active' => false]);
    [, $q] = mulaiSso($this);
    Http::fake([
        SSO.'/token' => Http::response(['id_token' => jwtSso(['sub' => 'u3', 'email' => 'n@x.id', 'nonce' => $q['nonce']])]),
        SSO.'/jwks' => Http::response(kunciSso()['jwks']),
    ]);
    $this->get('/sso/callback?code=abc&state='.$q['state'])->assertSessionHasErrors('email');
    $this->assertGuest();

    [, $q] = mulaiSso($this);
    $this->get('/sso/callback?code=abc&state=salah')->assertSessionHasErrors('email');

    User::factory()->create(['email' => 'b@x.id']);
    [, $q] = mulaiSso($this);
    Http::fake([
        SSO.'/token' => Http::response(['id_token' => jwtSso(['sub' => 'u4', 'email' => 'b@x.id', 'nonce' => 'lain'])]),
        SSO.'/jwks' => Http::response(kunciSso()['jwks']),
    ]);
    $this->get('/sso/callback?code=abc&state='.$q['state'])->assertSessionHasErrors('email');
    $this->assertGuest();
});

it('back-channel logout memutus sesi lokal dengan sid yang sama', function () {
    $user = User::factory()->create();
    DB::table('sessions')->insert(['id' => 'sesi-1', 'user_id' => $user->id, 'payload' => '', 'last_activity' => time()]);
    DB::table('sso_sesi')->insert(['sid' => 's9', 'session_id' => 'sesi-1', 'user_id' => $user->id, 'created_at' => now(), 'updated_at' => now()]);
    Http::fake([SSO.'/jwks' => Http::response(kunciSso()['jwks'])]);

    $token = jwtSso(['sub' => 'uuid', 'sid' => 's9', 'jti' => 'j1', 'events' => ['http://schemas.openid.net/event/backchannel-logout' => new stdClass]]);
    $this->post('/sso/backchannel-logout', ['logout_token' => $token])->assertNoContent();

    expect(DB::table('sessions')->where('id', 'sesi-1')->exists())->toBeFalse();
});

it('back-channel menolak token tanpa event, ber-nonce, atau bertanda tangan salah', function () {
    Http::fake([SSO.'/jwks' => Http::response(kunciSso()['jwks'])]);

    $this->post('/sso/backchannel-logout', ['logout_token' => jwtSso(['sid' => 's', 'sub' => 'x'])])->assertStatus(400);
    $this->post('/sso/backchannel-logout', ['logout_token' => jwtSso(['sid' => 's', 'sub' => 'x', 'nonce' => 'n', 'events' => ['http://schemas.openid.net/event/backchannel-logout' => new stdClass]])])->assertStatus(400);
    $this->post('/sso/backchannel-logout', ['logout_token' => 'bukan.jwt.valid'])->assertStatus(400);
});

it('wajib SSO menonaktifkan login kata sandi hanya bila SSO terkonfigurasi', function () {
    $user = User::factory()->create(['password' => 'rahasia123']);
    Settings::set('sso_wajib', '1');

    $this->post('/admin/masuk', ['email' => $user->email, 'password' => 'rahasia123'])->assertSessionHasErrors('email');

    Settings::set('sso_client_secret', '', true); // SSO tak lengkap → jangan mengunci
    $this->post('/admin/masuk', ['email' => $user->email, 'password' => 'rahasia123'])->assertRedirect('/admin');
});
