<?php

use App\Http\Controllers\Admin\RefundController;
use Illuminate\Support\Facades\Route;

/*
 * Customers request refunds in the storefront; the admin only decides them and
 * sets how long after delivery a request may be made. No create, edit or
 * delete: a refund request is a record of what the customer asked for.
 */

Route::controller(RefundController::class)
    ->prefix('refund-requests')
    ->name('refunds.')
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('settings', 'settings')->name('settings');
        Route::put('settings', 'updateSettings')->name('settings.update');
        Route::post('{refund}/approve', 'approve')->name('approve');
        Route::post('{refund}/reject', 'reject')->name('reject');
    });
