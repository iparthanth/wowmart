<?php

use App\Http\Controllers\Admin\NotificationController;
use Illuminate\Support\Facades\Route;

// Notifications: settings, the icon/type library, staff announcements (+history)
// and bulk SMS. Sends are POST actions; types are a full CRUD.

Route::controller(NotificationController::class)
    ->prefix('notifications')
    ->name('notifications.')
    ->group(function () {
        Route::get('settings', 'settings')->name('settings');
        Route::post('settings', 'saveSettings')->name('settings.save');

        Route::get('types', 'types')->name('types');
        Route::post('types', 'storeType')->name('types.store');
        Route::get('types/{type}/edit', 'editType')->name('types.edit');
        Route::put('types/{type}', 'updateType')->name('types.update');
        Route::delete('types/{type}', 'destroyType')->name('types.destroy');

        Route::get('custom', 'custom')->name('custom');
        Route::post('custom', 'sendCustom')->name('custom.store');
        Route::get('custom/history', 'history')->name('history');
        Route::delete('custom/{notification}', 'destroyCustom')->name('custom.destroy');
    });

Route::controller(NotificationController::class)
    ->prefix('bulk-sms')
    ->name('bulk-sms.')
    ->group(function () {
        Route::get('/', 'bulkSms')->name('index');
        Route::post('/', 'sendBulkSms')->name('send');
    });
