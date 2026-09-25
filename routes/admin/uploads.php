<?php

use App\Http\Controllers\Admin\UploadController;
use Illuminate\Support\Facades\Route;

// Path mirrors the reference panel (/admin/uploaded-files).

Route::controller(UploadController::class)
    ->prefix('uploaded-files')
    ->name('uploads.')
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::delete('{upload}', 'destroy')->name('destroy');
    });
