<?php

use App\Http\Controllers\Api\V1\CategoryController;
use Illuminate\Support\Facades\Route;

// Update is PUT only: CategoryRequest is a full replacement, so a partial PATCH
// would silently clear the parent, images and meta it did not send.
Route::apiResource('categories', CategoryController::class)->except('update');
Route::put('categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
Route::patch('categories/{category}/flags', [CategoryController::class, 'flags'])->name('categories.flags');
