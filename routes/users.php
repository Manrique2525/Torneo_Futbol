<?php

use App\Http\Controllers\UserController;

use Illuminate\Support\Facades\Route;


Route::middleware('auth')->group(function () {

    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    // En routes/web.php
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');

    // Puedes agregar más rutas para eliminar usuarios, etc.

});
