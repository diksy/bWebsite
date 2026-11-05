<?php

use App\Providers\AppServiceProvider;
use Livewire\LivewireServiceProvider;

return [
    AppServiceProvider::class,
    // Didaftarkan eksplisit: cache package-discovery di server (bootstrap/cache) tidak ikut deploy dan bisa basi.
    LivewireServiceProvider::class,
];
