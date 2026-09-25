<?php

use App\Http\Controllers\Admin\ProductController;
use Illuminate\Support\Facades\Route;

// Paths mirror the reference panel (/admin/products/all, /admin/products/store).

Route::controller(ProductController::class)
    ->prefix('products')
    ->name('products.')
    ->group(function () {
        Route::get('all', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::post('store', 'store')->name('store');
        Route::get('{product}/edit', 'edit')->name('edit');
        Route::put('{product}', 'update')->name('update');
        Route::delete('{product}', 'destroy')->name('destroy');
        Route::post('{product}/duplicate', 'duplicate')->name('duplicate');
        Route::post('{product}/toggle/{field}', 'toggle')
            ->whereIn('field', ['published', 'approved', 'featured', 'todays_deal'])
            ->name('toggle');
    });
