<?php

use App\Http\Controllers\CalendarioController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'tenant'])->group(function () {
    Route::get('torneos/{torneo}/calendario', [CalendarioController::class, 'show'])->name('calendario.show');
    Route::get('torneos/{torneo}/calendario/preview', [CalendarioController::class, 'preview'])->name('calendario.preview');
    Route::post('torneos/{torneo}/calendario', [CalendarioController::class, 'store'])->name('calendario.store');
    Route::put('torneos/{torneo}/calendario', [CalendarioController::class, 'update'])->name('calendario.update');
    Route::delete('torneos/{torneo}/calendario', [CalendarioController::class, 'destroy'])->name('calendario.destroy');
    Route::post('partidos/{partido}/sustituir', [CalendarioController::class, 'sustituir'])->name('calendario.sustituir');
});
