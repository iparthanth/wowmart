<?php

use App\Http\Controllers\Admin\ReportController;
use Illuminate\Support\Facades\Route;

// Read-only. Each screen also answers ?format=csv with the same rows as a download.

Route::controller(ReportController::class)
    ->prefix('reports')
    ->name('reports.')
    ->group(function () {
        Route::get('stock', 'stock')->name('stock');
        Route::get('in-house-sales', 'inHouseSales')->name('in-house-sales');
        Route::get('seller-sales', 'sellerSales')->name('seller-sales');
        Route::get('wishlist', 'wishlist')->name('wishlist');
    });
