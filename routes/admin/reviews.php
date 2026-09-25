<?php

use App\Http\Controllers\Admin\ReviewController;
use Illuminate\Support\Facades\Route;

// Customers write reviews in the storefront; the panel only hides or deletes them.

Route::controller(ReviewController::class)
    ->prefix('reviews')
    ->name('reviews.')
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::delete('{review}', 'destroy')->name('destroy');
        Route::post('{review}/toggle/{field}', 'toggle')
            ->whereIn('field', ['status'])
            ->name('toggle');
    });
