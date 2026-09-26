<?php

use App\Http\Controllers\Api\V1\SettingsController;
use Illuminate\Support\Facades\Route;

// GET|PUT /api/v1/settings/{screen} for every screen the registry declares.
// Secrets are masked on read (is_set only) and blank means keep on write.

Route::controller(SettingsController::class)
    ->prefix('settings')
    ->name('settings.')
    ->group(function () {
        Route::post('test-mail', 'sendTestEmail')->name('test-mail');
        Route::get('{screen}', 'show')->name('show');
        Route::put('{screen}', 'update')->name('update');
    });
