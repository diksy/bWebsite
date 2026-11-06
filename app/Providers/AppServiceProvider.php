<?php

namespace App\Providers;

use App\Services\Gambar\Pengonversi;
use App\Services\Gambar\WebpPengonversi;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(Pengonversi::class, WebpPengonversi::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Alpine versi CSP-safe agar script-src tidak butuh 'unsafe-eval'.
        config(['livewire.csp_safe' => true]);
    }
}
