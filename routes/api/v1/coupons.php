<?php

use App\Http\Controllers\Api\V1\CouponController;
use Illuminate\Support\Facades\Route;

Route::apiResource('coupons', CouponController::class);
Route::patch('coupons/{coupon}/flags', [CouponController::class, 'flags'])->name('coupons.flags');
