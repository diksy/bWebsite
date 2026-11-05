<?php

use Livewire\LivewireServiceProvider;

it('mendaftarkan provider Livewire secara eksplisit (tahan cache discovery basi)', function () {
    expect(require base_path('bootstrap/providers.php'))->toContain(LivewireServiceProvider::class);
});

it('mengompilasi direktif Livewire di Blade', function () {
    $html = Blade::render("@livewireStyles\n@livewireScripts");

    expect($html)->not->toContain('@livewireStyles')->not->toContain('@livewireScripts');
});
