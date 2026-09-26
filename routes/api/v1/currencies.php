<?php

use App\Http\Controllers\Api\V1\CurrencyController;
use Illuminate\Support\Facades\Route;

// Update is PUT only: CurrencyRequest is a full replacement.
// Deleting the system currency is a 409 (CurrencyService).

Route::apiResource('currencies', CurrencyController::class)->except('update');
Route::put('currencies/{currency}', [CurrencyController::class, 'update'])->name('currencies.update');
