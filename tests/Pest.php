<?php

use App\Support\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\RouteCollection;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->beforeEach(fn () => Settings::flush())
    ->in('Feature');

/** Daftarkan ulang rute setelah pengaturan path berubah (mensimulasikan request berikutnya di produksi). */
function muatUlangRute(): void
{
    Settings::flush();
    $router = app('router');
    $router->setRoutes(new RouteCollection);
    $router->middleware('web')->group(base_path('routes/web.php'));
    $router->getRoutes()->refreshNameLookups();
    $router->getRoutes()->refreshActionLookups();
}
