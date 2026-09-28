<?php

use App\Http\Controllers\Admin\SystemController;
use Illuminate\Support\Facades\Route;

// System: update bookkeeping, read-only server status, sitemap generator.
// Generation and deletion are POST actions; server status has no writes.

Route::controller(SystemController::class)
    ->prefix('system')
    ->name('system.')
    ->group(function () {
        Route::get('update', 'update')->name('update');
        Route::post('update', 'applyUpdate')->name('apply-update');

        Route::get('server-status', 'serverStatus')->name('server-status');

        Route::get('sitemap-generator', 'sitemaps')->name('sitemaps');
        Route::post('sitemap-generator', 'generateSitemaps')->name('sitemaps.generate');
        Route::post('sitemap-generator/{name}/delete', 'deleteSitemap')->name('sitemaps.delete');
    });
