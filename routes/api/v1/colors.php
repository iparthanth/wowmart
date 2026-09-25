<?php

use App\Http\Controllers\Api\V1\ColorController;
use Illuminate\Support\Facades\Route;

// Update is PUT only: ColorRequest is a full replacement.
Route::apiResource('colors', ColorController::class)->except('update');
Route::put('colors/{color}', [ColorController::class, 'update'])->name('colors.update');
