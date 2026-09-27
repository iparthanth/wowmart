<?php

use App\Http\Controllers\Admin\CarrierController;
use App\Http\Controllers\Admin\CityController;
use App\Http\Controllers\Admin\CountryController;
use App\Http\Controllers\Admin\StateController;
use App\Http\Controllers\Admin\ZoneController;
use Illuminate\Support\Facades\Route;

/*
 * The shipping area, mirroring the reference panel's URLs
 * (/admin/shipping-countries, ...). The carrier's billing setup (ranges and
 * their zone prices) hangs off the carrier edit page; the static
 * "carrier-ranges" segment comes before the bindings so they are not swallowed.
 */

Route::controller(CountryController::class)
    ->prefix('shipping-countries')
    ->name('countries.')
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::get('{country}/edit', 'edit')->name('edit');
        Route::put('{country}', 'update')->name('update');
        Route::delete('{country}', 'destroy')->name('destroy');
    });

Route::controller(StateController::class)
    ->prefix('shipping-states')
    ->name('states.')
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::get('{state}/edit', 'edit')->name('edit');
        Route::put('{state}', 'update')->name('update');
        Route::delete('{state}', 'destroy')->name('destroy');
    });

Route::controller(CityController::class)
    ->prefix('shipping-cities')
    ->name('cities.')
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::get('{city}/edit', 'edit')->name('edit');
        Route::put('{city}', 'update')->name('update');
        Route::delete('{city}', 'destroy')->name('destroy');
    });

Route::controller(ZoneController::class)
    ->prefix('shipping-zones')
    ->name('zones.')
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::get('{zone}/edit', 'edit')->name('edit');
        Route::put('{zone}', 'update')->name('update');
        Route::delete('{zone}', 'destroy')->name('destroy');
    });

Route::controller(CarrierController::class)
    ->prefix('shipping-carriers')
    ->name('carriers.')
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::get('{carrier}/edit', 'edit')->name('edit');
        Route::put('{carrier}', 'update')->name('update');
        Route::delete('{carrier}', 'destroy')->name('destroy');

        Route::post('{carrier}/ranges', 'storeRange')->name('ranges.store');
        Route::post('carrier-ranges/{carrier_range}/prices', 'storePrice')->name('ranges.prices.store');
        Route::delete('carrier-ranges/{carrier_range}', 'destroyRange')->name('ranges.destroy');
        Route::delete('carrier-range-prices/{carrier_range_price}', 'destroyPrice')->name('ranges.prices.destroy');
    });
