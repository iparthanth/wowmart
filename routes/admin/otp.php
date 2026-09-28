<?php

use App\Http\Controllers\Admin\OtpController;
use Illuminate\Support\Facades\Route;

// The OTP system: login toggles, OTP parameters and the SMS texts that carry
// them. Saves are POST from forms; template rows are PUT-updated individually.

Route::controller(OtpController::class)
    ->prefix('otp')
    ->name('otp.')
    ->group(function () {
        Route::get('login-configuration', 'loginConfiguration')->name('login');
        Route::post('login-configuration', 'saveLoginConfiguration')->name('login.save');

        Route::get('configuration', 'configuration')->name('configuration');
        Route::post('configuration', 'saveConfiguration')->name('configuration.save');

        Route::get('sms-templates', 'templates')->name('templates');
        Route::put('sms-templates/{template}', 'updateTemplate')->name('templates.update');
    });
