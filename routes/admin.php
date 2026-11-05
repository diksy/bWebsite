<?php

use App\Http\Controllers\Admin\DasborController;
use App\Http\Controllers\Admin\PengaturanSitusController;
use App\Http\Controllers\Admin\PenggunaController;
use App\Http\Controllers\Auth\MasukController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/masuk', [MasukController::class, 'form'])->name('masuk');
    Route::post('/masuk', [MasukController::class, 'masuk'])->middleware('throttle:6,1')->name('masuk.proses');
});

Route::post('/keluar', [MasukController::class, 'keluar'])->middleware('auth')->name('keluar');

Route::middleware(['auth', 'peran:admin,penulis'])->group(function () {
    Route::get('/', DasborController::class)->name('dasbor');
});

Route::middleware(['auth', 'peran:admin'])->group(function () {
    Route::get('/situs', [PengaturanSitusController::class, 'edit'])->name('pengaturan.situs');
    Route::put('/situs', [PengaturanSitusController::class, 'update'])->name('pengaturan.situs.simpan');
    Route::post('/situs/uji-sso', [PengaturanSitusController::class, 'ujiSso'])->name('pengaturan.situs.uji-sso');
    Route::resource('pengguna', PenggunaController::class)->except('show')->parameters(['pengguna' => 'pengguna']);
});
