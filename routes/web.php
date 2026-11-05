<?php

use App\Http\Controllers\PrototypeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PrototypeController::class, 'home'])->name('proto.home');
Route::get('/cari', [PrototypeController::class, 'cari'])->name('proto.cari');
Route::get('/kategori/{slug}', [PrototypeController::class, 'kategori'])->name('proto.kategori');
Route::get('/tag/{tag}', [PrototypeController::class, 'tag'])->name('proto.tag');
Route::get('/penulis/{slug}', [PrototypeController::class, 'penulis'])->name('proto.penulis');
Route::get('/artikel/{slug}', [PrototypeController::class, 'artikel'])->name('proto.artikel');
