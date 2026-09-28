<?php

use App\Http\Controllers\Admin\AffiliateController;
use Illuminate\Support\Facades\Route;

// The affiliate system: two configuration screens plus the users, referrals,
// withdrawals and logs lists. Approvals are POST decisions (actions), and
// product-sharing vs category-wise commissions cannot both be switched on.

Route::controller(AffiliateController::class)
    ->prefix('affiliate')
    ->name('affiliate.')
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'saveConfigurations')->name('configurations');

        Route::get('registration', 'registration')->name('registration');
        Route::post('registration', 'saveRegistration')->name('registration.save');

        Route::get('users', 'users')->name('users');
        Route::post('users/{affiliate}/decision', 'decideAffiliate')->name('users.decision');

        Route::get('referrals', 'referrals')->name('referrals');

        Route::get('withdrawals', 'withdrawals')->name('withdrawals');
        Route::post('withdrawals/{withdrawal}/decision', 'decideWithdrawal')->name('withdrawals.decision');

        Route::get('logs', 'logs')->name('logs');
    });
