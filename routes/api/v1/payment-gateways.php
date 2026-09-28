<?php

use App\Http\Controllers\Api\V1\PaymentGatewayController;
use Illuminate\Support\Facades\Route;

// Gateway credentials are PUT-replaced per card; manual methods are a full
// resource; offline decisions are POST actions guarded against re-decisions.

Route::get('payment-methods', [PaymentGatewayController::class, 'gatewaysIndex'])->name('payment-methods.index');
Route::get('payment-methods/{gateway}', [PaymentGatewayController::class, 'gatewaysShow'])->name('payment-methods.show');
Route::put('payment-methods/{gateway}', [PaymentGatewayController::class, 'gatewaysUpdate'])->name('payment-methods.update');

// Explicit bindings: the controller groups its methods per section instead of
// the plain resource names.

Route::prefix('manual-payment-methods')->name('manual-payment-methods.')->group(function () {
    Route::get('/', [PaymentGatewayController::class, 'manualIndex'])->name('index');
    Route::post('/', [PaymentGatewayController::class, 'manualStore'])->name('store');
    Route::get('{manualPaymentMethod}', [PaymentGatewayController::class, 'manualShow'])->name('show');
    Route::put('{manualPaymentMethod}', [PaymentGatewayController::class, 'manualUpdate'])->name('update');
    Route::delete('{manualPaymentMethod}', [PaymentGatewayController::class, 'manualDestroy'])->name('destroy');
});

Route::prefix('offline')->name('offline.')->group(function () {
    Route::get('orders', [PaymentGatewayController::class, 'offlineOrdersIndex'])->name('orders.index');
    Route::get('orders/{order}', [PaymentGatewayController::class, 'offlineOrdersShow'])->name('orders.show');
    Route::post('orders/{order}/mark-paid', [PaymentGatewayController::class, 'offlineOrdersMarkPaid'])->name('orders.mark-paid');

    Route::get('wallet', [PaymentGatewayController::class, 'walletIndex'])->name('wallet.index');
    Route::get('wallet/{recharge}', [PaymentGatewayController::class, 'walletShow'])->name('wallet.show');
    Route::post('wallet/{recharge}/decision', [PaymentGatewayController::class, 'walletDecision'])->name('wallet.decision');

    Route::get('package-payments', [PaymentGatewayController::class, 'packagesIndex'])->name('packages.index');
    Route::get('package-payments/{packagePayment}', [PaymentGatewayController::class, 'packagesShow'])->name('packages.show');
    Route::post('package-payments/{packagePayment}/decision', [PaymentGatewayController::class, 'packagesDecision'])->name('packages.decision');
});
