<?php

use App\Http\Controllers\Admin\PageController;
use Illuminate\Support\Facades\Route;

// Static pages, mirroring the reference panel's /admin/website/pages.
Route::controller(PageController::class)
    ->prefix('website/pages')
    ->name('pages.')
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::get('{page}/edit', 'edit')->name('edit');
        Route::put('{page}', 'update')->name('update');
        Route::delete('{page}', 'destroy')->name('destroy');
    });
