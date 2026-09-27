<?php

use App\Http\Controllers\Admin\MarketingController;
use Illuminate\Support\Facades\Route;

// The Marketing section, mirroring the reference panel's URLs. The "admin."
// name prefix comes from the route group in web.php.
Route::controller(MarketingController::class)->group(function () {
    Route::get('dynamic-popups', 'popups')->name('marketing.popups');
    Route::post('dynamic-popups', 'storePopup')->name('marketing.popups.store');
    Route::get('dynamic-popups/{dynamic_popup}/edit', 'editPopup')->name('marketing.popups.edit');
    Route::put('dynamic-popups/{dynamic_popup}', 'updatePopup')->name('marketing.popups.update');
    Route::delete('dynamic-popups/{dynamic_popup}', 'destroyPopup')->name('marketing.popups.destroy');

    Route::get('custom-alerts', 'alerts')->name('marketing.alerts');
    Route::post('custom-alerts', 'storeAlert')->name('marketing.alerts.store');
    Route::get('custom-alerts/{custom_alert}/edit', 'editAlert')->name('marketing.alerts.edit');
    Route::put('custom-alerts/{custom_alert}', 'updateAlert')->name('marketing.alerts.update');
    Route::delete('custom-alerts/{custom_alert}', 'destroyAlert')->name('marketing.alerts.destroy');

    Route::get('email-templates', 'templates')->name('marketing.templates');
    Route::post('email-templates', 'storeTemplate')->name('marketing.templates.store');
    Route::get('email-templates/{email_template}/edit', 'editTemplate')->name('marketing.templates.edit');
    Route::put('email-templates/{email_template}', 'updateTemplate')->name('marketing.templates.update');
    Route::delete('email-templates/{email_template}', 'destroyTemplate')->name('marketing.templates.destroy');

    Route::get('subscribers', 'subscribers')->name('marketing.subscribers');
    Route::delete('subscribers/{subscriber}', 'deleteSubscriber')->name('marketing.subscribers.destroy');

    Route::get('newsletters', 'newsletters')->name('marketing.newsletters');
    Route::post('newsletters', 'storeNewsletter')->name('marketing.newsletters.store');
    Route::post('newsletters/{newsletter}/send', 'sendNewsletter')->name('marketing.newsletters.send');
    Route::delete('newsletters/{newsletter}', 'deleteNewsletter')->name('marketing.newsletters.destroy');
});
