<?php

use App\Http\Controllers\ArbitroController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'tenant'])->group(function () {
    Route::resource('arbitros', ArbitroController::class);
});