<?php

use App\Http\Controllers\Api\V1\NoteController;
use Illuminate\Support\Facades\Route;

// Update is PUT only: NoteRequest is a full replacement.
Route::apiResource('notes', NoteController::class)->except('update');
Route::put('notes/{note}', [NoteController::class, 'update'])->name('notes.update');
