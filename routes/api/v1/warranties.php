<?php

use App\Http\Controllers\Api\V1\WarrantyController;
use Illuminate\Support\Facades\Route;

// Update is PUT only: WarrantyRequest is a full replacement.
Route::apiResource('warranties', WarrantyController::class)->except('update');
Route::put('warranties/{warranty}', [WarrantyController::class, 'update'])->name('warranties.update');
