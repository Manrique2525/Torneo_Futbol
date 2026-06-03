<?php

use App\Http\Controllers\PartidoEnVivoController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'tenant'])->group(function () {
    Route::get('partidos/{partido}/en-vivo', [PartidoEnVivoController::class, 'show'])
        ->name('partidos.en-vivo.show');

    Route::post('partidos/{partido}/iniciar', [PartidoEnVivoController::class, 'iniciar'])
        ->name('partidos.en-vivo.iniciar');

    Route::post('partidos/{partido}/descanso', [PartidoEnVivoController::class, 'descanso'])
        ->name('partidos.en-vivo.descanso');

    Route::post('partidos/{partido}/segunda-mitad', [PartidoEnVivoController::class, 'segundaMitad'])
        ->name('partidos.en-vivo.segunda-mitad');

    Route::post('partidos/{partido}/finalizar', [PartidoEnVivoController::class, 'finalizar'])
        ->name('partidos.en-vivo.finalizar');

    Route::post('partidos/{partido}/eventos', [PartidoEnVivoController::class, 'storeEvento'])
        ->name('partidos.en-vivo.eventos.store');

    Route::delete('partidos/eventos/{evento}', [PartidoEnVivoController::class, 'destroyEvento'])
        ->name('partidos.en-vivo.eventos.destroy');

    Route::post('partidos/{partido}/asistencias', [PartidoEnVivoController::class, 'storeAsistencias'])
        ->name('partidos.en-vivo.asistencias.store');
});
