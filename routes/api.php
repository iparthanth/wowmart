<?php

use App\Http\Controllers\Api\V1\AuthController;
use Illuminate\Support\Facades\Route;

/*
 * WowMart Admin API, version 1 - the same operations as the panel, for
 * Postman and future clients. Bearer tokens only (Laravel Sanctum).
 *
 * Every route except login sits behind auth:sanctum + admin, applied to the
 * group, so an endpoint is protected the moment its file exists. Each module
 * adds its endpoints in routes/api/v1/<module>.php.
 */

Route::prefix('v1')->name('api.v1.')->group(function () {
    Route::post('auth/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1')
        ->name('auth.login');

    Route::middleware(['auth:sanctum', 'admin', 'throttle:120,1'])->group(function () {
        Route::post('auth/logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::get('auth/me', [AuthController::class, 'me'])->name('auth.me');

        foreach (glob(__DIR__.'/api/v1/*.php') as $module) {
            require $module;
        }
    });
});
