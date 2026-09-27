<?php

use App\Http\Controllers\Api\V1\PageController;
use Illuminate\Support\Facades\Route;

// Update is PUT only: PageRequest is a full replacement.

Route::apiResource('pages', PageController::class)->except('update');
Route::put('pages/{page}', [PageController::class, 'update'])->name('pages.update');
