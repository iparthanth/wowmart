<?php

use App\Http\Controllers\Admin\PickupPointController;
use Illuminate\Support\Facades\Route;

// One page lists and creates, like colours.

Route::controller(PickupPointController::class)
    ->prefix('pickup-points')
    ->name('pickup-points.')
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::get('{pickup_point}/edit', 'edit')->name('edit');
        Route::put('{pickup_point}', 'update')->name('update');
        Route::delete('{pickup_point}', 'destroy')->name('destroy');
    });
