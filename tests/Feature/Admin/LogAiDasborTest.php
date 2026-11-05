<?php

use App\Models\AiLog;
use App\Models\AiRun;
use App\Models\Artikel;
use App\Models\Tema;
use App\Models\User;

beforeEach(fn () => $this->admin = User::factory()->create(['peran' => 'admin']));

it('log AI hanya untuk admin', function () {
    $this->actingAs(User::factory()->create())->get('/admin/log-ai')->assertForbidden();
    $this->actingAs($this->admin)->get('/admin/log-ai')->assertOk()->assertSee('Belum ada log');
});

it('log AI menampilkan token dan error per job, dengan filter error', function () {
    $tema = Tema::factory()->create(['nama' => 'Tema Uji']);
    $run = AiRun::create(['tema_id' => $tema->id, 'status' => 'gagal', 'pesan' => 'Pencarian web tidak menemukan sumber.']);
    AiLog::create(['ai_run_id' => $run->id, 'tahap' => 'ringkas', 'status' => 'ok', 'token_masuk' => 1200, 'token_keluar' => 300, 'durasi_ms' => 2500]);
    AiLog::create(['ai_run_id' => $run->id, 'tahap' => 'outline', 'status' => 'error', 'pesan' => '429 Too Many Requests']);
    AiLog::create(['ai_run_id' => $run->id, 'tahap' => 'cari_web', 'status' => 'ok', 'token_masuk' => 5, 'token_keluar' => 5, 'created_at' => now()->subDays(20)]);

    $this->actingAs($this->admin)->get('/admin/log-ai')
        ->assertSee('Tema Uji')->assertSee('1.200')->assertSee('429 Too Many Requests')->assertSee('2,5 dtk')
        ->assertSee('Token 7 hari: 1.500')->assertSee('30 hari: 1.510')->assertSee('Error 7 hari: 1')
        ->assertSee('Pencarian web tidak menemukan sumber.');

    $this->actingAs($this->admin)->get('/admin/log-ai?status=error')->assertSee('429 Too Many Requests')->assertDontSee('1.200');
});

it('dashboard admin menampilkan hitungan, token 7 hari, error, dan draf AI', function () {
    Artikel::factory()->count(2)->create(['dibuat_ai' => true]);
    Artikel::factory()->terbit()->create();
    $run = AiRun::create(['status' => 'selesai']);
    AiLog::create(['ai_run_id' => $run->id, 'tahap' => 'draf', 'status' => 'ok', 'token_masuk' => 700, 'token_keluar' => 300]);
    AiLog::create(['ai_run_id' => $run->id, 'tahap' => 'draf', 'status' => 'error']);

    $this->actingAs($this->admin)->get('/admin')->assertOk()
        ->assertSee('Token AI (7 hari)')->assertSee('1.000')->assertSee('Error AI (7 hari)')->assertSee('Draf AI menunggu tinjauan');
});

it('dashboard penulis hanya menghitung artikelnya dan tanpa panel AI', function () {
    $penulis = User::factory()->create();
    Artikel::factory()->create(['user_id' => $penulis->id, 'judul' => 'Draf Saya']);
    Artikel::factory()->create(['judul' => 'Draf Orang Lain']);

    $this->actingAs($penulis)->get('/admin')->assertOk()->assertSee('Draf Saya')->assertDontSee('Draf Orang Lain')->assertDontSee('Token AI');
});
