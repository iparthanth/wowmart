<?php

use App\Http\Controllers\Api\V1\PermissionController;
use App\Http\Controllers\Api\V1\RoleController;
use Illuminate\Support\Facades\Route;

// Update is PUT only: RoleRequest is a full replacement.
// Deleting a role staff still use is a 409 (RoleService).

Route::get('permissions', [PermissionController::class, 'index'])->name('permissions.index');

Route::apiResource('roles', RoleController::class)->except('update');
Route::put('roles/{role}', [RoleController::class, 'update'])->name('roles.update');
