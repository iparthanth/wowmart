<?php

use App\Http\Controllers\Api\V1\ReportController;
use Illuminate\Support\Facades\Route;

// Read-only aggregates, same filters as the panel screens.

Route::controller(ReportController::class)
    ->prefix('reports')
    ->name('reports.')
    ->group(function () {
        Route::get('stock', 'stock')->name('stock');
        Route::get('in-house-sales', 'inHouseSales')->name('in-house-sales');
        Route::get('seller-sales', 'sellerSales')->name('seller-sales');
        Route::get('wishlist', 'wishlist')->name('wishlist');
    });
