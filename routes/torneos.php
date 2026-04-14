<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TorneoController;

Route::middleware(['auth', 'verified', 'tenant'])->group(function () {
Route::resource('torneos', TorneoController::class);


});
