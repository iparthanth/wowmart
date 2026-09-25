<?php

use App\Http\Controllers\Admin\CouponController;
use Illuminate\Support\Facades\Route;

// Paths mirror the reference panel (/admin/coupon, /admin/coupon/create).

Route::controller(CouponController::class)
    ->prefix('coupon')
    ->name('coupons.')
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('{coupon}/edit', 'edit')->name('edit');
        Route::put('{coupon}', 'update')->name('update');
        Route::delete('{coupon}', 'destroy')->name('destroy');
        Route::post('{coupon}/toggle/{field}', 'toggle')
            ->whereIn('field', ['status'])
            ->name('toggle');
    });
