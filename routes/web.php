<?php

use App\Http\Controllers\PrototypeController;
use App\Support\Settings;
use Illuminate\Support\Facades\Route;

// Halaman publik (sementara masih prototype; diganti bertahap pada fase build)
Route::get('/', [PrototypeController::class, 'home'])->name('proto.home');
Route::get('/cari', [PrototypeController::class, 'cari'])->name('proto.cari');
Route::get('/kategori/{slug}', [PrototypeController::class, 'kategori'])->name('proto.kategori');
Route::get('/tag/{tag}', [PrototypeController::class, 'tag'])->name('proto.tag');
Route::get('/penulis/{slug}', [PrototypeController::class, 'penulis'])->name('proto.penulis');
Route::get('/artikel/{slug}', [PrototypeController::class, 'artikel'])->name('proto.artikel');

// Panel admin: path bisa diubah di Pengaturan situs (default "admin")
Route::prefix(trim(Settings::get('admin_path', 'admin'), '/'))
    ->name('admin.')
    ->group(base_path('routes/admin.php'));
