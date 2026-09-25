<?php

use App\Http\Controllers\Admin\NoteController;
use Illuminate\Support\Facades\Route;

// Paths mirror the reference panel (/admin/note, /admin/note/create).

Route::controller(NoteController::class)
    ->prefix('note')
    ->name('notes.')
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('{note}/edit', 'edit')->name('edit');
        Route::put('{note}', 'update')->name('update');
        Route::delete('{note}', 'destroy')->name('destroy');
    });
