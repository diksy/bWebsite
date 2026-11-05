<?php

use App\Jobs\Ai\BuatDraf;
use App\Jobs\Ai\BuatOutline;
use App\Jobs\Ai\CariWeb;
use App\Jobs\Ai\RingkasSumber;
use App\Models\AiLog;
use App\Models\AiRun;
use App\Models\Artikel;
use App\Models\Kategori;
use App\Models\Tema;
use App\Models\User;
use App\Services\Ai\Pipeline\Json;
use App\Services\Ai\Pipeline\Pipeline;
use App\Services\Ai\Pipeline\PipelineException;
use App\Support\Settings;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;

function siapkanAi(): void
{
    Settings::set('ai_base_url', 'https://api.contoh.test/v1');
    Settings::set('ai_api_key', 'sk-uji', true);
    Settings::set('ai_model', 'model-x');
    Settings::set('search_api_key', 'tvly-uji', true);
}

function fakeAi(?array $draf = null, ?array $outline = null): void
{
    $isi = "Paragraf pembuka tentang seo umkm yang cukup panjang untuk lolos validasi panjang minimal.\n\n## Bagian Satu\n\n".str_repeat('Kalimat isi yang informatif. ', 20);
    $draf ??= ['judul' => 'Panduan SEO UMKM', 'meta_description' => 'Deskripsi meta.', 'isi_markdown' => $isi, 'faq' => [['q' => 'Apa?', 'a' => 'Ini.'], ['q' => '', 'a' => 'x']], 'tag' => ['SEO', 'UMKM Lokal']];
    $outline ??= ['judul' => 'Panduan SEO UMKM', 'kata_kunci_utama' => 'seo umkm', 'bagian' => ['Apa itu SEO', 'Langkah praktis']];

    $balasan = [
        json_encode($outline), // dipakai urutan: ringkas → outline → draf
    ];
    $urutan = 0;
    Http::fake([
        'api.tavily.com/*' => Http::response(['results' => [
            ['title' => 'Sumber A', 'url' => 'https://a.test/x', 'content' => 'isi sumber a'],
            ['title' => 'Sumber B', 'url' => 'javascript:alert(1)', 'content' => 'bukan http'],
        ]]),
        'api.contoh.test/*' => function () use (&$urutan, $outline, $draf) {
            $urutan++;
            $teks = match ($urutan) {
                1 => 'Ringkasan poin sumber 1.',
                2 => "Berikut outline:\n```json\n".json_encode($outline)."\n```",
                default => json_encode($draf),
            };

            return Http::response(['choices' => [['message' => ['content' => $teks]]], 'usage' => ['prompt_tokens' => 100 * $urutan, 'completion_tokens' => 10]]);
        },
    ]);
}

beforeEach(function () {
    siapkanAi();
    $this->tema = Tema::factory()->create([
        'kategori_id' => Kategori::factory()->create()->id,
        'kata_kunci' => 'seo umkm, blog bisnis',
    ]);
});

it('menjalankan pipeline 4 tahap dan menghasilkan satu draf (tidak pernah terbit)', function () {
    fakeAi();

    $run = app(Pipeline::class)->mulai($this->tema);

    $run->refresh();
    expect($run->status)->toBe('selesai')->and($run->artikel_id)->not->toBeNull();

    $a = Artikel::find($run->artikel_id);
    expect($a->status)->toBe('draf')
        ->and($a->terbit_pada)->toBeNull()
        ->and($a->dibuat_ai)->toBeTrue()
        ->and($a->user_id)->toBe($this->tema->user_id)
        ->and($a->kategori_id)->toBe($this->tema->kategori_id)
        ->and($a->tema_id)->toBe($this->tema->id)
        ->and($a->kata_kunci)->toBe('seo umkm')
        ->and($a->faq)->toHaveCount(1)                                       // FAQ kosong dibuang
        ->and($a->sumber)->toBe([['judul' => 'Sumber A', 'url' => 'https://a.test/x']])  // hanya URL http(s)
        ->and($a->tag->pluck('slug')->sort()->values()->all())->toBe(['seo', 'umkm-lokal'])
        ->and($this->tema->fresh()->terakhir_jalan)->not->toBeNull();
    expect(Artikel::diterbitkan()->count())->toBe(0);
});

it('mencatat log per tahap dengan token', function () {
    fakeAi();
    $run = app(Pipeline::class)->mulai($this->tema);

    $log = AiLog::where('ai_run_id', $run->id)->orderBy('id')->get();
    expect($log->pluck('tahap')->all())->toBe(['cari_web', 'ringkas', 'outline', 'draf'])
        ->and($log->every(fn ($l) => $l->status === 'ok'))->toBeTrue()
        ->and($log->sum('token_masuk'))->toBe(100 + 200 + 300)
        ->and($log->sum('token_keluar'))->toBe(30);
});

it('draf dari AI yang mencoba status terbit tetap menjadi draf', function () {
    $isi = str_repeat('Isi panjang yang cukup. ', 30);
    fakeAi(['judul' => 'X', 'meta_description' => 'm', 'isi_markdown' => $isi, 'faq' => [], 'tag' => [], 'status' => 'terbit', 'terbit_pada' => now()->toIso8601String()]);

    $run = app(Pipeline::class)->mulai($this->tema);

    expect(Artikel::find($run->fresh()->artikel_id)->status)->toBe('draf');
});

it('isi HTML/script dari AI tidak lolos ke halaman', function () {
    fakeAi(['judul' => 'Aman', 'meta_description' => 'm', 'isi_markdown' => str_repeat('Teks. ', 80).'<script>alert(1)</script> [x](javascript:alert(1))', 'faq' => [], 'tag' => []]);
    $run = app(Pipeline::class)->mulai($this->tema);

    $html = Artikel::find($run->fresh()->artikel_id)->isi_html;
    expect($html)->not->toContain('<script>')->not->toContain('javascript:');
});

it('tahap gagal menandai run gagal dan mencatat error; tahap berikutnya tidak jalan', function () {
    Http::fake([
        'api.tavily.com/*' => Http::response(['results' => [['title' => 'A', 'url' => 'https://a.test', 'content' => 'x']]]),
        'api.contoh.test/*' => Http::response(['error' => ['message' => 'rate limit']], 429),
    ]);

    try {
        app(Pipeline::class)->mulai($this->tema);
    } catch (Throwable) {
        // dengan antrean sync, pengecualian diteruskan; di produksi job ditandai gagal oleh worker
    }

    $run = AiRun::first();
    expect($run->status)->toBe('gagal')->and($run->pesan)->toContain('429');
    $err = AiLog::where('status', 'error')->first();
    expect($err->tahap)->toBe('ringkas')->and($err->pesan)->toContain('rate limit');
    expect(AiLog::where('tahap', 'outline')->exists())->toBeFalse()->and(Artikel::count())->toBe(0);
});

it('outline tidak lengkap atau isi terlalu pendek menggagalkan run tanpa membuat artikel', function () {
    fakeAi(null, ['judul' => '', 'bagian' => []]);
    try {
        app(Pipeline::class)->mulai($this->tema);
    } catch (Throwable) {
    }
    expect(AiRun::first()->status)->toBe('gagal')->and(Artikel::count())->toBe(0);
});

it('menolak mulai bila pengaturan belum lengkap, kuota penuh, atau masih berjalan', function () {
    Settings::set('ai_api_key', '', true);
    expect(fn () => app(Pipeline::class)->mulai($this->tema))->toThrow(PipelineException::class, 'Pengaturan AI belum lengkap');

    siapkanAi();
    Settings::set('search_api_key', '', true);
    expect(fn () => app(Pipeline::class)->mulai($this->tema))->toThrow(PipelineException::class, 'pencarian web');

    siapkanAi();
    $this->tema->update(['batas_per_minggu' => 1]);
    Artikel::factory()->create(['tema_id' => $this->tema->id, 'dibuat_ai' => true]);
    expect(fn () => app(Pipeline::class)->mulai($this->tema))->toThrow(PipelineException::class, 'Batas 1 draf/minggu');

    $this->tema->update(['batas_per_minggu' => 5]);
    AiRun::create(['tema_id' => $this->tema->id, 'status' => 'berjalan']);
    expect(fn () => app(Pipeline::class)->mulai($this->tema))->toThrow(PipelineException::class, 'masih punya proses');
});

it('kata kunci diputar tiap eksekusi', function () {
    Bus::fake(); // rantai tidak dijalankan; hanya menguji pemilihan topik

    $r1 = app(Pipeline::class)->mulai($this->tema);
    expect($r1->topik)->toBe('seo umkm');
    Bus::assertChained([CariWeb::class, RingkasSumber::class, BuatOutline::class, BuatDraf::class]);

    $r1->update(['status' => 'selesai']);
    $r2 = app(Pipeline::class)->mulai($this->tema->fresh());
    expect($r2->topik)->toBe('blog bisnis');

    $r2->update(['status' => 'selesai']);
    expect(app(Pipeline::class)->mulai($this->tema->fresh())->topik)->toBe('seo umkm');
});

it('tema:jalankan hanya memproses tema aktif, jatuh tempo, dan berkuota', function () {
    fakeAi();
    $jeda = Tema::factory()->create(['aktif' => false]);
    $belum = Tema::factory()->create(['frekuensi' => 'bulanan', 'terakhir_jalan' => now()->subDay()]);

    $this->artisan('tema:jalankan')->expectsOutput('1 pipeline dimulai.')->assertSuccessful();

    expect(AiRun::where('tema_id', $this->tema->id)->count())->toBe(1)
        ->and(AiRun::where('tema_id', $jeda->id)->count())->toBe(0)
        ->and(AiRun::where('tema_id', $belum->id)->count())->toBe(0);
});

it('tema:jalankan mencatat kegagalan prasyarat ke log, bukan crash', function () {
    Settings::set('ai_api_key', '', true);

    $this->artisan('tema:jalankan')->assertSuccessful();

    expect(AiRun::first())->status->toBe('gagal')->pesan->toContain('Pengaturan AI belum lengkap');
});

it('tema:jalankan terdaftar di scheduler', function () {
    $this->artisan('schedule:list')->expectsOutputToContain('tema:jalankan')->assertSuccessful();
});

it('admin dapat menjalankan tema sekarang; penulis tidak', function () {
    fakeAi();
    $admin = User::factory()->create(['peran' => 'admin']);

    $this->actingAs(User::factory()->create())->post("/admin/tema/{$this->tema->id}/jalankan")->assertForbidden();
    $this->actingAs($admin)->post("/admin/tema/{$this->tema->id}/jalankan")->assertSessionHas('status');
    expect(Artikel::where('dibuat_ai', true)->count())->toBe(1);

    $this->actingAs($admin)->post("/admin/tema/{$this->tema->id}/jalankan")->assertSessionHasErrors('jalankan'); // kuota/terakhir
});

it('Json::urai toleran pagar kode dan menolak non-JSON', function () {
    expect(Json::urai("Ini:\n```json\n{\"a\":1}\n```"))->toBe(['a' => 1]);
    expect(fn () => Json::urai('tidak ada json'))->toThrow(PipelineException::class);
    expect(fn () => Json::urai('{rusak'))->toThrow(PipelineException::class);
});

it('job BuatDraf tidak berisi jalur penerbitan otomatis', function () {
    $sumber = file_get_contents((new ReflectionClass(BuatDraf::class))->getFileName());
    expect($sumber)->not->toContain('Artikel::TERBIT')->not->toContain("'terbit'");
});

it('log-ai:bersihkan menghapus data lama saja', function () {
    $lama = AiRun::create(['status' => 'selesai', 'created_at' => now()->subDays(200)]);
    AiLog::create(['ai_run_id' => $lama->id, 'tahap' => 'draf', 'status' => 'ok', 'created_at' => now()->subDays(200)]);
    $baru = AiRun::create(['status' => 'selesai']);
    AiLog::create(['ai_run_id' => $baru->id, 'tahap' => 'draf', 'status' => 'ok']);

    $this->artisan('log-ai:bersihkan')->assertSuccessful();

    expect(AiRun::count())->toBe(1)->and(AiLog::count())->toBe(1);
});

it('scheduler memuat antrean database, terbit terjadwal, dan tema', function () {
    $this->artisan('schedule:list')
        ->expectsOutputToContain('queue:work')
        ->expectsOutputToContain('artikel:terbitkan-terjadwal')
        ->expectsOutputToContain('tema:jalankan')
        ->assertSuccessful();
});
