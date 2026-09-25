<?php

use App\Http\Controllers\Admin\StaffController;
use Illuminate\Support\Facades\Route;

// Staff members and administrators, listed together like the reference panel.
// Route-model binding resolves any user row; the service guards admins.

Route::controller(StaffController::class)
    ->prefix('staff')
    ->name('staff.')
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::get('{member}/edit', 'edit')->name('edit');
        Route::put('{member}', 'update')->name('update');
        Route::delete('{member}', 'destroy')->name('destroy');
    });
