<?php

use App\Http\Controllers\Admin\CurrencyController;
use Illuminate\Support\Facades\Route;

// One page lists and creates, like colours. The system currency is protected.

Route::controller(CurrencyController::class)
    ->prefix('currencies')
    ->name('currencies.')
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::get('{currency}/edit', 'edit')->name('edit');
        Route::put('{currency}', 'update')->name('update');
        Route::post('{currency}/system', 'makeSystem')->name('system');
        Route::delete('{currency}', 'destroy')->name('destroy');
    });
