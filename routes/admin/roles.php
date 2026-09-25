<?php

use App\Http\Controllers\Admin\RoleController;
use Illuminate\Support\Facades\Route;

// One page lists and creates, like colors.

Route::controller(RoleController::class)
    ->prefix('roles')
    ->name('roles.')
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::get('{role}/edit', 'edit')->name('edit');
        Route::put('{role}', 'update')->name('update');
        Route::delete('{role}', 'destroy')->name('destroy');
    });
