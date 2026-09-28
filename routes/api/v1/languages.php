<?php

use App\Http\Controllers\Api\V1\LanguageController;
use Illuminate\Support\Facades\Route;

// Update is PUT only: LanguageRequest is a full replacement.
// Deleting the default language is a 409 (LanguageService).

Route::apiResource('languages', LanguageController::class)->except('update');
Route::put('languages/{language}', [LanguageController::class, 'update'])->name('languages.update');
