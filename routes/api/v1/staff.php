<?php

use App\Http\Controllers\Api\V1\StaffController;
use Illuminate\Support\Facades\Route;

// Update is PUT only: StaffRequest is a full replacement.
// Guards live in StaffService: self-delete and the last admin are 409s.

Route::apiResource('staff', StaffController::class)->parameters(['staff' => 'staff'])->except('update');
Route::put('staff/{staff}', [StaffController::class, 'update'])->name('staff.update');
