<?php

use App\Http\Controllers\Api\V1\TaxController;
use Illuminate\Support\Facades\Route;

// Update is PUT only: TaxRequest is a full replacement.
// Deleting a tax with product assignments is a 409 (TaxService).

Route::apiResource('taxes', TaxController::class)->except('update');
Route::put('taxes/{tax}', [TaxController::class, 'update'])->name('taxes.update');
