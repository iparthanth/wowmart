<?php

use App\Http\Controllers\Api\V1\AffiliateController;
use Illuminate\Support\Facades\Route;

// PUT replaces what it names (settings, registration form, affiliate status,
// withdrawal status+note); everything else is read-only or a DELETE.

Route::get('affiliate/settings', [AffiliateController::class, 'settingsShow'])->name('affiliate.settings.show');
Route::put('affiliate/settings', [AffiliateController::class, 'settingsUpdate'])->name('affiliate.settings.update');
Route::get('affiliate/registration', [AffiliateController::class, 'registrationShow'])->name('affiliate.registration.show');
Route::put('affiliate/registration', [AffiliateController::class, 'registrationUpdate'])->name('affiliate.registration.update');

Route::prefix('affiliate/users')->name('affiliate.users.')->group(function () {
    Route::get('/', [AffiliateController::class, 'usersIndex'])->name('index');
    Route::get('{affiliate}', [AffiliateController::class, 'usersShow'])->name('show');
    Route::put('{affiliate}', [AffiliateController::class, 'usersUpdate'])->name('update');
    Route::delete('{affiliate}', [AffiliateController::class, 'usersDestroy'])->name('destroy');
});

Route::get('affiliate/referrals', [AffiliateController::class, 'referralsIndex'])->name('affiliate.referrals.index');

Route::prefix('affiliate/withdrawals')->name('affiliate.withdrawals.')->group(function () {
    Route::get('/', [AffiliateController::class, 'withdrawalsIndex'])->name('index');
    Route::get('{withdrawal}', [AffiliateController::class, 'withdrawalsShow'])->name('show');
    Route::put('{withdrawal}', [AffiliateController::class, 'withdrawalsUpdate'])->name('update');
    Route::delete('{withdrawal}', [AffiliateController::class, 'withdrawalsDestroy'])->name('destroy');
});

Route::prefix('affiliate/logs')->name('affiliate.logs.')->group(function () {
    Route::get('/', [AffiliateController::class, 'logsIndex'])->name('index');
    Route::get('{log}', [AffiliateController::class, 'logsShow'])->name('show');
});
