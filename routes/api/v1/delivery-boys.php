<?php

use App\Http\Controllers\Api\V1\DeliveryBoyController;
use Illuminate\Support\Facades\Route;

// Update is PUT only: DeliveryBoyRequest is a full replacement.
// Payments and collections are POST-only rows in a money trail - no edits.

Route::controller(DeliveryBoyController::class)
    ->name('delivery-boys.')
    ->group(function () {
        Route::get('delivery-boys', 'index')->name('index');
        Route::post('delivery-boys', 'store')->name('store');
        Route::get('delivery-boys/{delivery_boy}', 'show')->name('show');
        Route::put('delivery-boys/{delivery_boy}', 'update')->name('update');
        Route::delete('delivery-boys/{delivery_boy}', 'destroy')->name('destroy');

        Route::get('delivery-boys-payment-histories', 'payments')->name('payments');
        Route::get('delivery-boys-collection-histories', 'collections')->name('collections');
        Route::post('delivery-boys-payment-histories', 'storePayment')->name('payments.store');
        Route::post('delivery-boys-collection-histories', 'storeCollection')->name('collections.store');
    });
