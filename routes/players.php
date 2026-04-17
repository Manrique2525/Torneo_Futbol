<?php

use App\Http\Controllers\PlayerController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'tenant'])->group(function () {
    Route::resource('players', PlayerController::class);
});