<?php

use App\Http\Controllers\Api\V1\CouponController;
use Illuminate\Support\Facades\Route;

// Update is PUT only: CouponRequest is a full replacement, so a partial PATCH
// that left out status would silently switch the coupon off.
Route::apiResource('coupons', CouponController::class)->except('update');
Route::put('coupons/{coupon}', [CouponController::class, 'update'])->name('coupons.update');
Route::patch('coupons/{coupon}/flags', [CouponController::class, 'flags'])->name('coupons.flags');
