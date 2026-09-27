<?php

use App\Http\Controllers\Api\V1\MarketingController;
use Illuminate\Support\Facades\Route;

// The Marketing section over the API. Update is PUT only: each request class
// is a full replacement.

Route::get('dynamic-popups', [MarketingController::class, 'popupIndex'])->name('dynamic-popups.index');
Route::post('dynamic-popups', [MarketingController::class, 'popupStore'])->name('dynamic-popups.store');
Route::get('dynamic-popups/{dynamic_popup}', [MarketingController::class, 'popupShow'])->name('dynamic-popups.show');
Route::put('dynamic-popups/{dynamic_popup}', [MarketingController::class, 'popupUpdate'])->name('dynamic-popups.update');
Route::delete('dynamic-popups/{dynamic_popup}', [MarketingController::class, 'popupDestroy'])->name('dynamic-popups.destroy');

Route::get('custom-alerts', [MarketingController::class, 'alertIndex'])->name('custom-alerts.index');
Route::post('custom-alerts', [MarketingController::class, 'alertStore'])->name('custom-alerts.store');
Route::get('custom-alerts/{custom_alert}', [MarketingController::class, 'alertShow'])->name('custom-alerts.show');
Route::put('custom-alerts/{custom_alert}', [MarketingController::class, 'alertUpdate'])->name('custom-alerts.update');
Route::delete('custom-alerts/{custom_alert}', [MarketingController::class, 'alertDestroy'])->name('custom-alerts.destroy');

Route::get('email-templates', [MarketingController::class, 'templateIndex'])->name('email-templates.index');
Route::post('email-templates', [MarketingController::class, 'templateStore'])->name('email-templates.store');
Route::get('email-templates/{email_template}', [MarketingController::class, 'templateShow'])->name('email-templates.show');
Route::put('email-templates/{email_template}', [MarketingController::class, 'templateUpdate'])->name('email-templates.update');
Route::delete('email-templates/{email_template}', [MarketingController::class, 'templateDestroy'])->name('email-templates.destroy');

Route::get('subscribers', [MarketingController::class, 'subscriberIndex'])->name('subscribers.index');
Route::delete('subscribers/{subscriber}', [MarketingController::class, 'subscriberDestroy'])->name('subscribers.destroy');

Route::get('newsletters', [MarketingController::class, 'newsletterIndex'])->name('newsletters.index');
Route::post('newsletters', [MarketingController::class, 'newsletterStore'])->name('newsletters.store');
Route::post('newsletters/{newsletter}/send', [MarketingController::class, 'newsletterSend'])->name('newsletters.send');
Route::delete('newsletters/{newsletter}', [MarketingController::class, 'newsletterDestroy'])->name('newsletters.destroy');
