<?php

use App\Http\Controllers\CanchaController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'tenant'])->group(function () {
    Route::resource('canchas', CanchaController::class);
});
