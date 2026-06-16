<?php

use App\Http\Controllers\EstadisticasController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'tenant'])->group(function () {
    Route::get('torneos/{torneo}/estadisticas', [EstadisticasController::class, 'index'])
        ->name('estadisticas.index');

    Route::get('torneos/{torneo}/estadisticas/equipo/{torneoEquipo}', [EstadisticasController::class, 'equipo'])
        ->name('estadisticas.equipo');
});
