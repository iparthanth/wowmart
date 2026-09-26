<?php

use App\Http\Controllers\Admin\SettingsController;
use Illuminate\Support\Facades\Route;

// One generic screen for every settings screen the registry declares.
// test-mail is POST under the same prefix; the {screen} GET can't shadow it.

Route::controller(SettingsController::class)
    ->prefix('settings')
    ->name('settings.')
    ->group(function () {
        Route::post('test-mail', 'sendTestEmail')->name('test-mail');
        Route::get('{screen}', 'show')->name('show');
        Route::put('{screen}', 'update')->name('update');
    });
