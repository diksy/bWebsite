<?php

use App\Http\Controllers\PrototypeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PrototypeController::class, 'home'])->name('proto.home');
