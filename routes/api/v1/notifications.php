<?php

use App\Http\Controllers\Api\V1\NotificationController;
use Illuminate\Support\Facades\Route;

// Settings are GET/PUT; types are a full CRUD; custom notifications and bulk
// SMS are sent with POST and readable as lists.

Route::get('notifications/settings', [NotificationController::class, 'settingsShow'])->name('notifications.settings.show');
Route::put('notifications/settings', [NotificationController::class, 'settingsUpdate'])->name('notifications.settings.update');

Route::prefix('notifications/types')->name('notifications.types.')->group(function () {
    Route::get('/', [NotificationController::class, 'typesIndex'])->name('index');
    Route::post('/', [NotificationController::class, 'typesStore'])->name('store');
    Route::get('{notificationType}', [NotificationController::class, 'typesShow'])->name('show');
    Route::put('{notificationType}', [NotificationController::class, 'typesUpdate'])->name('update');
    Route::delete('{notificationType}', [NotificationController::class, 'typesDestroy'])->name('destroy');
});

Route::prefix('notifications/custom')->name('notifications.custom.')->group(function () {
    Route::get('/', [NotificationController::class, 'customIndex'])->name('index');
    Route::post('/', [NotificationController::class, 'customStore'])->name('store');
    Route::delete('{customNotification}', [NotificationController::class, 'customDestroy'])->name('destroy');
});

Route::prefix('bulk-sms')->name('bulk-sms.')->group(function () {
    Route::get('/', [NotificationController::class, 'bulkSmsIndex'])->name('index');
    Route::post('/', [NotificationController::class, 'bulkSmsStore'])->name('store');
});
