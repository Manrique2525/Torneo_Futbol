<?php

use App\Http\Controllers\PartidoController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'tenant'])->group(function () {
    Route::resource('partidos', PartidoController::class);
});
