<?php

use App\Http\Controllers\Admin\WarrantyController;
use Illuminate\Support\Facades\Route;

// One page lists and creates, like brands.

Route::controller(WarrantyController::class)
    ->prefix('warranties')
    ->name('warranties.')
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::get('{warranty}/edit', 'edit')->name('edit');
        Route::put('{warranty}', 'update')->name('update');
        Route::delete('{warranty}', 'destroy')->name('destroy');
    });
