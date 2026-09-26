<?php

use App\Http\Controllers\Admin\TaxController;
use Illuminate\Support\Facades\Route;

// The Vat & Tax page: one list of taxes plus the per-product assignments.
// Static "product-tax" segments come before "{tax}" so they are not swallowed.

Route::controller(TaxController::class)
    ->prefix('taxes')
    ->name('taxes.')
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::get('{tax}/edit', 'edit')->name('edit');
        Route::put('{tax}', 'update')->name('update');
        Route::delete('{tax}', 'destroy')->name('destroy');

        Route::post('product-taxes', 'assignStore')->name('product-taxes.store');
        Route::get('product-taxes/{product_tax}/edit', 'assignEdit')->name('product-taxes.edit');
        Route::put('product-taxes/{product_tax}', 'assignUpdate')->name('product-taxes.update');
        Route::delete('product-taxes/{product_tax}', 'assignDestroy')->name('product-taxes.destroy');
    });
