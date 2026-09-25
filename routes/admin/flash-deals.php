<?php

use App\Http\Controllers\Admin\FlashDealController;
use Illuminate\Support\Facades\Route;

// Paths mirror the reference panel (/admin/flash_deals, /admin/flash_deals/create).

Route::controller(FlashDealController::class)
    ->prefix('flash_deals')
    ->name('flash-deals.')
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('{flashDeal}/edit', 'edit')->name('edit');
        Route::put('{flashDeal}', 'update')->name('update');
        Route::delete('{flashDeal}', 'destroy')->name('destroy');
        Route::post('{flashDeal}/toggle/{field}', 'toggle')
            ->whereIn('field', ['status', 'featured'])
            ->name('toggle');
    });
