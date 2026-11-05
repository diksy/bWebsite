<?php

use App\Jobs\KirimIndexNow;
use App\Livewire\Admin\ArtikelEditor;
use App\Models\Artikel;
use App\Models\User;
use App\Services\Seo\IndexNow;
use App\Support\Settings;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;

it('command menerbitkan artikel terjadwal yang waktunya tiba, bukan yang belum', function () {
    $siap = Artikel::factory()->create(['status' => 'terjadwal', 'terbit_pada' => now()->subMinute()]);
    $nanti = Artikel::factory()->create(['status' => 'terjadwal', 'terbit_pada' => now()->addHour()]);
    $draf = Artikel::factory()->create(['status' => 'draf', 'terbit_pada' => now()->subDay()]);

    $this->artisan('artikel:terbitkan-terjadwal')->expectsOutput('1 artikel diterbitkan.')->assertSuccessful();

    expect($siap->fresh()->status)->toBe('terbit')
        ->and($nanti->fresh()->status)->toBe('terjadwal')
        ->and($draf->fresh()->status)->toBe('draf');
});

it('command terdaftar di scheduler tiap menit', function () {
    $this->artisan('schedule:list')->expectsOutputToContain('artikel:terbitkan-terjadwal')->assertSuccessful();
});

it('mengantrekan IndexNow saat artikel menjadi terbit, tidak untuk draf atau simpan ulang', function () {
    Queue::fake();

    $a = Artikel::factory()->create();
    Queue::assertNotPushed(KirimIndexNow::class);

    $a->update(['status' => 'terbit', 'terbit_pada' => now()]);
    Queue::assertPushed(KirimIndexNow::class, 1);

    $a->update(['judul' => 'Judul diubah']);
    Queue::assertPushed(KirimIndexNow::class, 1);

    Artikel::factory()->terbit()->create();
    Queue::assertPushed(KirimIndexNow::class, 2);
});

it('terjadwal yang diterbitkan scheduler memicu IndexNow', function () {
    Queue::fake();
    Artikel::factory()->create(['status' => 'terjadwal', 'terbit_pada' => now()->subMinute()]);

    $this->artisan('artikel:terbitkan-terjadwal');

    Queue::assertPushed(KirimIndexNow::class, 1);
});

it('job mengirim URL artikel ke IndexNow dengan kunci dan keyLocation', function () {
    Settings::set('indexnow_aktif', '1');
    Queue::fake();
    Http::fake(['api.indexnow.org/*' => Http::response('', 200)]);
    $a = Artikel::factory()->terbit()->create(['slug' => 'baru']);

    (new KirimIndexNow($a->id))->handle(app(IndexNow::class));

    Http::assertSent(function ($r) use ($a) {
        $d = $r->data();

        return $r->url() === IndexNow::ENDPOINT
            && in_array($a->url(), $d['urlList'], true)
            && $d['key'] === Settings::get('indexnow_key')
            && str_ends_with($d['keyLocation'], Settings::get('indexnow_key').'.txt');
    });
});

it('tidak mengirim bila IndexNow nonaktif atau artikel belum tayang', function () {
    Queue::fake();
    Http::fake();
    $a = Artikel::factory()->terbit()->create();

    Settings::set('indexnow_aktif', '0');
    (new KirimIndexNow($a->id))->handle(app(IndexNow::class));

    Settings::set('indexnow_aktif', '1');
    $draf = Artikel::factory()->create();
    (new KirimIndexNow($draf->id))->handle(app(IndexNow::class));

    Http::assertNothingSent();
});

it('job gagal (dicoba ulang) bila IndexNow membalas error', function () {
    Queue::fake();
    Settings::set('indexnow_aktif', '1');
    Http::fake(['api.indexnow.org/*' => Http::response('', 403)]);
    $a = Artikel::factory()->terbit()->create();

    expect(fn () => (new KirimIndexNow($a->id))->handle(app(IndexNow::class)))->toThrow(RuntimeException::class);
});

it('file kunci IndexNow dilayani hanya untuk kunci yang benar', function () {
    $kunci = app(IndexNow::class)->kunci();

    $this->get("/{$kunci}.txt")->assertOk()->assertSee($kunci);
    $this->get('/'.str_repeat('a', 32).'.txt')->assertNotFound();
    expect($kunci)->toMatch('/^[a-f0-9]{32}$/');
});

it('editor: terbit dengan tanggal masa depan menjadi terjadwal', function () {
    $admin = User::factory()->create(['peran' => 'admin']);

    Livewire::actingAs($admin)->test(ArtikelEditor::class)
        ->set('judul', 'Nanti')->set('isi', 'Isi')->set('status', 'terbit')
        ->set('terbit_pada', now()->addDays(2)->format('Y-m-d\TH:i'))->call('simpan')->assertHasNoErrors();

    expect(Artikel::first()->status)->toBe('terjadwal');
});
