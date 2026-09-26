<?php

use App\Http\Controllers\Admin\DeliveryBoyController;
use Illuminate\Support\Facades\Route;

// Paths mirror the reference panel (/admin/delivery-boys, payment/collection
// histories). Static history paths live in their own group.

Route::controller(DeliveryBoyController::class)
    ->name('delivery-boys.')
    ->group(function () {
        Route::get('delivery-boys-payment-histories', 'payments')->name('payments');
        Route::get('delivery-boys-collection-histories', 'collections')->name('collections');
        Route::post('delivery-boys-payment-histories', 'storePayment')->name('payments.store');
        Route::post('delivery-boys-collection-histories', 'storeCollection')->name('collections.store');

        Route::get('delivery-boys', 'index')->name('index');
        Route::post('delivery-boys', 'store')->name('store');
        Route::get('delivery-boys/{delivery_boy}/edit', 'edit')->name('edit');
        Route::put('delivery-boys/{delivery_boy}', 'update')->name('update');
        Route::delete('delivery-boys/{delivery_boy}', 'destroy')->name('destroy');
    });
