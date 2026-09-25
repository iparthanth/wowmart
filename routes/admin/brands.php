<?php

use App\Http\Controllers\Admin\BrandController;
use Illuminate\Support\Facades\Route;

// One page lists and creates, like the reference panel (/admin/brands).

Route::controller(BrandController::class)
    ->prefix('brands')
    ->name('brands.')
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::get('{brand}/edit', 'edit')->name('edit');
        Route::put('{brand}', 'update')->name('update');
        Route::delete('{brand}', 'destroy')->name('destroy');
        Route::post('{brand}/toggle/{field}', 'toggle')
            ->whereIn('field', ['top'])
            ->name('toggle');
    });
