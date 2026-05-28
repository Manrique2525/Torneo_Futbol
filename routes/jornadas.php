<?php

use App\Http\Controllers\JornadaController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'tenant'])->group(function () {
    Route::resource('jornadas', JornadaController::class);
});
