<?php

use App\Http\Controllers\Admin\ArtikelController;
use App\Http\Controllers\Admin\DasborController;
use App\Http\Controllers\Admin\KategoriController;
use App\Http\Controllers\Admin\PengaturanAiController;
use App\Http\Controllers\Admin\PengaturanSitusController;
use App\Http\Controllers\Admin\PenggunaController;
use App\Http\Controllers\Admin\TagController;
use App\Http\Controllers\Admin\TemaController;
use App\Http\Controllers\Auth\MasukController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/masuk', [MasukController::class, 'form'])->name('masuk');
    Route::post('/masuk', [MasukController::class, 'masuk'])->middleware('throttle:6,1')->name('masuk.proses');
});

Route::post('/keluar', [MasukController::class, 'keluar'])->middleware('auth')->name('keluar');

Route::middleware(['auth', 'peran:admin,penulis'])->group(function () {
    Route::get('/', DasborController::class)->name('dasbor');
    Route::get('/artikel', [ArtikelController::class, 'index'])->name('artikel.index');
    Route::get('/artikel/baru', [ArtikelController::class, 'create'])->name('artikel.create');
    Route::get('/artikel/{artikel}/sunting', [ArtikelController::class, 'edit'])->name('artikel.edit');
    Route::delete('/artikel/{artikel}', [ArtikelController::class, 'destroy'])->name('artikel.destroy');
});

Route::middleware(['auth', 'peran:admin'])->group(function () {
    Route::get('/kategori', [KategoriController::class, 'index'])->name('kategori.index');
    Route::post('/kategori', [KategoriController::class, 'store'])->name('kategori.store');
    Route::put('/kategori/{kategori}', [KategoriController::class, 'update'])->name('kategori.update');
    Route::delete('/kategori/{kategori}', [KategoriController::class, 'destroy'])->name('kategori.destroy');
    Route::post('/tag', [TagController::class, 'store'])->name('tag.store');
    Route::delete('/tag/{tag}', [TagController::class, 'destroy'])->name('tag.destroy');
    Route::get('/pengaturan-ai', [PengaturanAiController::class, 'edit'])->name('pengaturan.ai');
    Route::put('/pengaturan-ai', [PengaturanAiController::class, 'update'])->name('pengaturan.ai.simpan');
    Route::post('/pengaturan-ai/uji-ai', [PengaturanAiController::class, 'ujiAi'])->name('pengaturan.ai.uji-ai');
    Route::post('/pengaturan-ai/uji-pencarian', [PengaturanAiController::class, 'ujiPencarian'])->name('pengaturan.ai.uji-pencarian');
    Route::get('/situs', [PengaturanSitusController::class, 'edit'])->name('pengaturan.situs');
    Route::put('/situs', [PengaturanSitusController::class, 'update'])->name('pengaturan.situs.simpan');
    Route::post('/situs/uji-sso', [PengaturanSitusController::class, 'ujiSso'])->name('pengaturan.situs.uji-sso');
    Route::resource('tema', TemaController::class)->except('show')->parameters(['tema' => 'tema']);
    Route::resource('pengguna', PenggunaController::class)->except('show')->parameters(['pengguna' => 'pengguna']);
});
