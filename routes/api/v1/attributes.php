<?php

use App\Http\Controllers\Api\V1\AttributeController;
use App\Http\Controllers\Api\V1\AttributeValueController;
use Illuminate\Support\Facades\Route;

// Updates are PUT only: the request bodies are full replacements.
Route::apiResource('attributes', AttributeController::class)->except('update');
Route::put('attributes/{attribute}', [AttributeController::class, 'update'])->name('attributes.update');

// Values are listed and added under their attribute, then addressed by their own id.
Route::get('attributes/{attribute}/values', [AttributeValueController::class, 'index'])->name('attributes.values.index');
Route::post('attributes/{attribute}/values', [AttributeValueController::class, 'store'])->name('attributes.values.store');
Route::put('attribute-values/{value}', [AttributeValueController::class, 'update'])->name('attribute-values.update');
Route::delete('attribute-values/{value}', [AttributeValueController::class, 'destroy'])->name('attribute-values.destroy');
