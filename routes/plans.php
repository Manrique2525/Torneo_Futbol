<?php

use App\Http\Controllers\PlanController;
use Illuminate\Support\Facades\Route;

// super_admin check is inside the controller constructor
Route::resource('plans', PlanController::class)->except(['show', 'create', 'edit']);
