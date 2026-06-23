<?php

use App\Http\Controllers\InscripcionPagoController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'tenant'])->group(function () {
    Route::get('torneos/{torneo}/pagos', [InscripcionPagoController::class, 'index'])->name('pagos.index');
    Route::post('torneos/{torneo}/pagos', [InscripcionPagoController::class, 'store'])->name('pagos.store');
    Route::patch('torneos/{torneo}/pagos/{inscripcionPago}/confirmar', [InscripcionPagoController::class, 'confirmar'])->name('pagos.confirmar');
    Route::patch('torneos/{torneo}/pagos/{inscripcionPago}/rechazar', [InscripcionPagoController::class, 'rechazar'])->name('pagos.rechazar');
});
