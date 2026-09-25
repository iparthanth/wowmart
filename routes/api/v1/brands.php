<?php

use App\Http\Controllers\Api\V1\BrandController;
use Illuminate\Support\Facades\Route;

// Update is PUT only: BrandRequest is a full replacement, so a partial PATCH
// would silently clear the logo, top flag and meta it did not send.
Route::apiResource('brands', BrandController::class)->except('update');
Route::put('brands/{brand}', [BrandController::class, 'update'])->name('brands.update');
Route::patch('brands/{brand}/flags', [BrandController::class, 'flags'])->name('brands.flags');
