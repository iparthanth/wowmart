<?php

use App\Http\Controllers\Api\V1\ProductController;
use Illuminate\Support\Facades\Route;

// Update is PUT only: every omitted field is cleared (see ProductController::update),
// so a partial PATCH would wipe the product.
Route::apiResource('products', ProductController::class)->except('update');
Route::put('products/{product}', [ProductController::class, 'update'])->name('products.update');
Route::post('products/{product}/duplicate', [ProductController::class, 'duplicate'])->name('products.duplicate');
Route::patch('products/{product}/flags', [ProductController::class, 'flags'])->name('products.flags');
