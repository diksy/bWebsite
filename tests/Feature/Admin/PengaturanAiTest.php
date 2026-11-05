<?php

use App\Models\User;
use App\Services\Ai\AiClient;
use App\Support\Settings;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

beforeEach(fn () => $this->admin = User::factory()->create(['peran' => 'admin']));

function dataAi(array $ganti = []): array
{
    return $ganti + [
        'ai_base_url' => 'https://api.contoh.test/v1/', 'ai_api_key' => 'sk-rahasia', 'ai_model' => 'model-x',
        'search_provider' => 'tavily', 'search_api_key' => 'tvly-rahasia',
    ];
}

it('hanya admin yang boleh mengakses', function () {
    $this->actingAs(User::factory()->create())->get('/admin/pengaturan-ai')->assertForbidden();
    $this->actingAs($this->admin)->get('/admin/pengaturan-ai')->assertOk();
});

it('menyimpan pengaturan dengan key terenkripsi dan tak pernah menampilkannya', function () {
    $this->actingAs($this->admin)->put('/admin/pengaturan-ai', dataAi())->assertRedirect('/admin/pengaturan-ai');

    expect(Settings::get('ai_base_url'))->toBe('https://api.contoh.test/v1')
        ->and(Settings::get('ai_api_key'))->toBe('sk-rahasia')
        ->and(DB::table('settings')->where('key', 'ai_api_key')->value('value'))->not->toContain('sk-rahasia');

    $this->actingAs($this->admin)->get('/admin/pengaturan-ai')->assertDontSee('sk-rahasia')->assertDontSee('tvly-rahasia');
});

it('mempertahankan key lama bila kolom dikosongkan', function () {
    $this->actingAs($this->admin)->put('/admin/pengaturan-ai', dataAi());
    $this->actingAs($this->admin)->put('/admin/pengaturan-ai', dataAi(['ai_api_key' => '', 'ai_model' => 'model-y']));

    expect(Settings::get('ai_api_key'))->toBe('sk-rahasia')->and(Settings::get('ai_model'))->toBe('model-y');
});

it('memvalidasi input', function () {
    $this->actingAs($this->admin)->put('/admin/pengaturan-ai', ['ai_base_url' => 'bukan-url', 'search_provider' => 'bing'])
        ->assertSessionHasErrors(['ai_base_url', 'ai_model', 'search_provider']);
});

it('uji koneksi model berhasil dan menghitung token', function () {
    $this->actingAs($this->admin)->put('/admin/pengaturan-ai', dataAi());
    Http::fake(['api.contoh.test/*' => Http::response(['choices' => [['message' => ['content' => 'OK']]], 'usage' => ['prompt_tokens' => 9, 'completion_tokens' => 1]])]);

    $this->actingAs($this->admin)->post('/admin/pengaturan-ai/uji-ai')->assertSessionHas('status', fn ($s) => str_contains($s, '10 token'));
    Http::assertSent(fn ($r) => $r->url() === 'https://api.contoh.test/v1/chat/completions' && $r->hasHeader('Authorization', 'Bearer sk-rahasia'));
});

it('uji koneksi model gagal menampilkan pesan error', function () {
    $this->actingAs($this->admin)->put('/admin/pengaturan-ai', dataAi());
    Http::fake(['api.contoh.test/*' => Http::response(['error' => ['message' => 'kunci salah']], 401)]);

    $this->actingAs($this->admin)->post('/admin/pengaturan-ai/uji-ai')->assertSessionHasErrors('ai');
});

it('AiClient menolak bila pengaturan belum lengkap', function () {
    expect(fn () => app(AiClient::class)->chat([['role' => 'user', 'content' => 'hai']]))->toThrow(RuntimeException::class);
});

it('uji pencarian web lewat adapter Tavily', function () {
    $this->actingAs($this->admin)->put('/admin/pengaturan-ai', dataAi());
    Http::fake(['api.tavily.com/*' => Http::response(['results' => [['title' => 'A', 'url' => 'https://a.test', 'content' => 'isi']]])]);

    $this->actingAs($this->admin)->post('/admin/pengaturan-ai/uji-pencarian')->assertSessionHas('status');
});
