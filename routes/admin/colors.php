<?php

use App\Http\Controllers\Admin\ColorController;
use Illuminate\Support\Facades\Route;

// One page lists and creates, like brands.

Route::controller(ColorController::class)
    ->prefix('colors')
    ->name('colors.')
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::get('{color}/edit', 'edit')->name('edit');
        Route::put('{color}', 'update')->name('update');
        Route::delete('{color}', 'destroy')->name('destroy');
    });
