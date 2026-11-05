<?php

use App\Http\Controllers\PublikController;
use App\Http\Controllers\SsoController;
use App\Support\Settings;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Support\Facades\Route;

// Panel admin: path bisa diubah di Pengaturan situs (default "admin"). Didaftarkan lebih dulu.
Route::prefix(trim((string) Settings::get('admin_path', 'admin'), '/'))
    ->name('admin.')
    ->group(base_path('routes/admin.php'));

// SSO (OIDC). Path tetap agar URI terdaftar di server SSO tidak berubah saat path admin diganti.
Route::prefix('sso')->name('admin.sso.')->group(function () {
    Route::get('/mulai', [SsoController::class, 'mulai'])->name('mulai');
    Route::get('/callback', [SsoController::class, 'callback'])->name('callback');
    Route::post('/backchannel-logout', [SsoController::class, 'backchannel'])
        ->withoutMiddleware([PreventRequestForgery::class])->name('backchannel');
});

// Halaman publik
Route::get('/', [PublikController::class, 'beranda'])->name('beranda');
Route::get('/cari', [PublikController::class, 'cari'])->name('cari');
Route::get('/kategori/{slug}', [PublikController::class, 'kategori'])->name('kategori');
Route::get('/tag/{slug}', [PublikController::class, 'tag'])->name('tag');
Route::get('/penulis/{slug}', [PublikController::class, 'penulis'])->name('penulis');

// Artikel: /{path-artikel}/{slug}; path bisa dikosongkan di pengaturan. Path lama dialihkan 301.
$pathArtikel = trim((string) Settings::get('artikel_path', 'artikel'), '/');
$slugRegex = '[a-z0-9]+(?:-[a-z0-9]+)*';

if ($pathArtikel !== '') {
    Route::get("/{$pathArtikel}/{slug}", [PublikController::class, 'artikel'])->where('slug', $slugRegex)->name('artikel');
    foreach ((array) json_decode((string) Settings::get('artikel_path_lama', '[]'), true) as $lama) {
        if (is_string($lama) && $lama !== '' && $lama !== $pathArtikel) {
            Route::get("/{$lama}/{slug}", [PublikController::class, 'artikelPathLama'])->where('slug', $slugRegex);
        }
    }
    // Sebelumnya tanpa path: /{slug} dialihkan ke URL baru bila artikelnya ada.
    Route::get('/{slug}', [PublikController::class, 'artikelTanpaPath'])->where('slug', $slugRegex);
} else {
    Route::get('/{slug}', [PublikController::class, 'artikel'])->where('slug', $slugRegex)->name('artikel');
    foreach ((array) json_decode((string) Settings::get('artikel_path_lama', '[]'), true) as $lama) {
        if (is_string($lama) && $lama !== '') {
            Route::get("/{$lama}/{slug}", [PublikController::class, 'artikelPathLama'])->where('slug', $slugRegex);
        }
    }
}
