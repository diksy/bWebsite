<?php

use App\Http\Controllers\PrototypeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PrototypeController::class, 'home'])->name('proto.home');
Route::get('/cari', [PrototypeController::class, 'cari'])->name('proto.cari');
Route::get('/kategori/{slug}', [PrototypeController::class, 'kategori'])->name('proto.kategori');
Route::get('/tag/{tag}', [PrototypeController::class, 'tag'])->name('proto.tag');
Route::get('/penulis/{slug}', [PrototypeController::class, 'penulis'])->name('proto.penulis');
Route::get('/artikel/{slug}', [PrototypeController::class, 'artikel'])->name('proto.artikel');

Route::prefix('admin')->group(function () {
    Route::get('/masuk', [PrototypeController::class, 'login'])->name('proto.login');
    Route::get('/', [PrototypeController::class, 'adminDashboard'])->name('proto.admin');
    Route::get('/artikel', [PrototypeController::class, 'adminArtikel'])->name('proto.admin.artikel');
    Route::get('/artikel/{id}/sunting', [PrototypeController::class, 'adminEditor'])->name('proto.admin.editor');
    Route::get('/pengaturan', [PrototypeController::class, 'adminPengaturan'])->name('proto.admin.pengaturan');
    Route::get('/tema', [PrototypeController::class, 'adminTema'])->name('proto.admin.tema');
    Route::get('/situs', [PrototypeController::class, 'adminSitus'])->name('proto.admin.situs');
    Route::get('/log-ai', [PrototypeController::class, 'adminLog'])->name('proto.admin.log');
});
