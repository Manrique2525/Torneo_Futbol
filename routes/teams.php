<?php


use App\Http\Controllers\TeamController;
use Illuminate\Support\Facades\Route;


Route::middleware(['auth', 'verified', 'tenant'])->group(function () {
    Route::resource('teams', TeamController::class);

    // Puedes agregar más rutas para eliminar usuarios, etc.

});
