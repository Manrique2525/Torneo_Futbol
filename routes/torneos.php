<?php

use App\Http\Controllers\TorneoController;
use App\Http\Controllers\TorneoEquipoController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'tenant'])->group(function () {
    Route::resource('torneos', TorneoController::class);

    Route::prefix('torneos/{torneo}')->name('torneos.')->group(function () {
        Route::get('equipos', [TorneoEquipoController::class, 'index'])->name('equipos.index');
        Route::post('equipos', [TorneoEquipoController::class, 'store'])->name('equipos.store');
        Route::put('equipos/{torneoEquipo}', [TorneoEquipoController::class, 'update'])->name('equipos.update');
        Route::post('equipos/{torneoEquipo}/aprobar', [TorneoEquipoController::class, 'aprobar'])->name('equipos.aprobar');
        Route::post('equipos/{torneoEquipo}/rechazar', [TorneoEquipoController::class, 'rechazar'])->name('equipos.rechazar');
        Route::patch('equipos/{torneoEquipo}/grupo', [TorneoEquipoController::class, 'asignarGrupo'])->name('equipos.asignar-grupo');
        Route::patch('equipos/{torneoEquipo}/seed', [TorneoEquipoController::class, 'asignarSeed'])->name('equipos.asignar-seed');
    });
});
