<?php

use App\Http\Controllers\Api\V1\ReviewController;
use Illuminate\Support\Facades\Route;

// No store or update: customers write reviews in the storefront, the admin only moderates.
Route::apiResource('reviews', ReviewController::class)->only(['index', 'show', 'destroy']);
Route::patch('reviews/{review}/flags', [ReviewController::class, 'flags'])->name('reviews.flags');
