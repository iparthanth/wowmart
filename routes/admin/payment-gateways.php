<?php

use App\Http\Controllers\Admin\PaymentGatewayController;
use Illuminate\Support\Facades\Route;

// Payment gateways: per-gateway credential cards, manual payment methods and
// the offline system's verification lists. Approvals are POST decisions that
// the service guards against double decisions (409 on the API).

Route::get('payment-methods', [PaymentGatewayController::class, 'index'])->name('payment-methods.index');
Route::post('payment-methods/{gateway}', [PaymentGatewayController::class, 'saveGateway'])->name('payment-methods.save');

Route::controller(PaymentGatewayController::class)
    ->prefix('manual-payment-methods')
    ->name('manual-methods.')
    ->group(function () {
        Route::get('/', 'manualMethods')->name('index');
        Route::post('/', 'storeManualMethod')->name('store');
        Route::get('{method}/edit', 'editManualMethod')->name('edit');
        Route::put('{method}', 'updateManualMethod')->name('update');
        Route::delete('{method}', 'destroyManualMethod')->name('destroy');
    });

Route::controller(PaymentGatewayController::class)
    ->prefix('offline')
    ->name('offline.')
    ->group(function () {
        Route::get('orders', 'offlineOrders')->name('orders');
        Route::post('orders/{order}/mark-paid', 'markOrderPaid')->name('orders.mark-paid');

        Route::get('wallet', 'offlineWallet')->name('wallet');
        Route::post('wallet/{recharge}/decision', 'decideRecharge')->name('wallet.decision');

        Route::get('customer-package-payments', 'offlinePackages')->defaults('type', 'customer')->name('packages.customer');
        Route::get('seller-package-payments', 'offlinePackages')->defaults('type', 'seller')->name('packages.seller');
        Route::post('package-payments/{payment}/decision', 'decidePackagePayment')->name('packages.decision');
    });
