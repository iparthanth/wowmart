<?php

use App\Http\Controllers\Api\V1\PickupPointController;
use Illuminate\Support\Facades\Route;

// Update is PUT only: PickupPointRequest is a full replacement.

Route::apiResource('pickup-points', PickupPointController::class)->except('update');
Route::put('pickup-points/{pickup_point}', [PickupPointController::class, 'update'])->name('pickup-points.update');
