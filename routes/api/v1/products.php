<?php

use App\Http\Controllers\Api\V1\ProductController;
use Illuminate\Support\Facades\Route;

Route::apiResource('products', ProductController::class);
Route::post('products/{product}/duplicate', [ProductController::class, 'duplicate'])->name('products.duplicate');
Route::patch('products/{product}/flags', [ProductController::class, 'flags'])->name('products.flags');
