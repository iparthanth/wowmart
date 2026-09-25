<?php

use App\Http\Controllers\Api\V1\FlashDealController;
use Illuminate\Support\Facades\Route;

// Update is PUT only: FlashDealRequest is a full replacement, so a partial PATCH
// would silently clear the banner and colours it did not send.
Route::apiResource('flash-deals', FlashDealController::class)->parameters(['flash-deals' => 'flashDeal'])->except('update');
Route::put('flash-deals/{flashDeal}', [FlashDealController::class, 'update'])->name('flash-deals.update');
Route::patch('flash-deals/{flashDeal}/flags', [FlashDealController::class, 'flags'])->name('flash-deals.flags');
