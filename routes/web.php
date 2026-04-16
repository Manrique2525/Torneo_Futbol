<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// ── Public ──────────────────────────────────────────
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin'    => Route::has('login'),
        'canRegister' => Route::has('register'),

        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
        'planes' => \App\Models\Plan::active()->ordered()->get(),

    ]);
})->name('home');

// ── Auth required (no tenant) ───────────────────────
Route::middleware(['auth', 'verified', 'tenant'])->group(function () {

    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ── Tenant modules (auth + tenant isolation) ────────
Route::middleware(['auth', 'verified', 'tenant'])->group(function () {

    Route::group([], base_path('routes/roles.php'));
    // Route::group([], base_path('routes/tournaments.php'));
    // Route::group([], base_path('routes/teams.php'));
    // Route::group([], base_path('routes/players.php'));
    // Route::group([], base_path('routes/matches.php'));

     Route::group([], base_path('routes/plans.php'));

});

// ── Auth routes (Breeze/Fortify) ────────────────────
require __DIR__ . '/auth.php';
require __DIR__ . '/users.php';
require __DIR__ . '/torneos.php';
require __DIR__.'/teams.php';

