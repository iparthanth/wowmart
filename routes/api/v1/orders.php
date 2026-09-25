<?php

use App\Http\Controllers\Api\V1\OrderController;
use Illuminate\Support\Facades\Route;

// Bound by order code, like the panel. No create or delete: orders come from
// the storefront and are financial records (see routes/admin/orders.php).

Route::controller(OrderController::class)
    ->prefix('orders')
    ->name('orders.')
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('{order:code}', 'show')->name('show');
        Route::patch('{order:code}/delivery-status', 'deliveryStatus')->name('delivery-status');
        Route::patch('{order:code}/payment-status', 'paymentStatus')->name('payment-status');
    });
