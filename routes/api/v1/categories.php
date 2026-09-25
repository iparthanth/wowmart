<?php

use App\Http\Controllers\Api\V1\CategoryController;
use Illuminate\Support\Facades\Route;

Route::apiResource('categories', CategoryController::class);
Route::patch('categories/{category}/flags', [CategoryController::class, 'flags'])->name('categories.flags');
