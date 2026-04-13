<?php

use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserRoleController;
use Illuminate\Support\Facades\Route;

Route::resource('roles', RoleController::class)->except(['show', 'create', 'edit']);

Route::get('users/{user}/permissions', [UserRoleController::class, 'show'])->name('users.permissions');
Route::post('users/{user}/roles', [UserRoleController::class, 'assign'])->name('users.roles.assign');
Route::delete('users/{user}/roles', [UserRoleController::class, 'revoke'])->name('users.roles.revoke');
Route::post('users/{user}/permissions', [UserRoleController::class, 'givePermission'])->name('users.permissions.give');
Route::delete('users/{user}/permissions', [UserRoleController::class, 'revokePermission'])->name('users.permissions.revoke');
