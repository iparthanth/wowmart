<?php

use App\Http\Controllers\Api\V1\FlashDealController;
use Illuminate\Support\Facades\Route;

Route::apiResource('flash-deals', FlashDealController::class)->parameters(['flash-deals' => 'flashDeal']);
Route::patch('flash-deals/{flashDeal}/flags', [FlashDealController::class, 'flags'])->name('flash-deals.flags');
