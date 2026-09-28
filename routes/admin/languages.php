<?php

use App\Http\Controllers\Admin\LanguageController;
use Illuminate\Support\Facades\Route;

// One page lists and creates, like currencies. The default language is protected.

Route::controller(LanguageController::class)
    ->prefix('languages')
    ->name('languages.')
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::get('{language}/edit', 'edit')->name('edit');
        Route::put('{language}', 'update')->name('update');
        Route::post('{language}/default', 'makeDefault')->name('default');
        Route::delete('{language}', 'destroy')->name('destroy');
    });
