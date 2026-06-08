<?php

use App\Http\Controllers\StandingsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'tenant'])->group(function () {
    Route::get('torneos/{torneo}/posiciones', [StandingsController::class, 'index'])
        ->name('standings.index');

    Route::post('torneos/{torneo}/posiciones/recalcular', [StandingsController::class, 'recalcular'])
        ->name('standings.recalcular');
});
