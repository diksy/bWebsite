<?php

use App\Models\User;

it('membuat admin lewat command', function () {
    $this->artisan('app:buat-admin', ['email' => 'a@x.id', 'nama' => 'Admin', '--password' => 'rahasia123'])->assertSuccessful();

    expect(User::where('email', 'a@x.id')->first())
        ->peran->toBe('admin')
        ->slug->toBe('admin');
});
