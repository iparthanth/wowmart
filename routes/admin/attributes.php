<?php

use App\Http\Controllers\Admin\AttributeController;
use App\Http\Controllers\Admin\AttributeValueController;
use Illuminate\Support\Facades\Route;

// One page lists and creates, like the reference panel (/admin/attributes);
// the edit page renames the attribute and manages its values.

Route::controller(AttributeController::class)
    ->prefix('attributes')
    ->name('attributes.')
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::get('{attribute}/edit', 'edit')->name('edit');
        Route::put('{attribute}', 'update')->name('update');
        Route::delete('{attribute}', 'destroy')->name('destroy');
    });

Route::controller(AttributeValueController::class)
    ->name('attributes.values.')
    ->group(function () {
        Route::post('attributes/{attribute}/values', 'store')->name('store');
        Route::put('attribute-values/{value}', 'update')->name('update');
        Route::delete('attribute-values/{value}', 'destroy')->name('destroy');
    });
