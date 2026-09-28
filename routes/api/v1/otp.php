<?php

use App\Http\Controllers\Api\V1\OtpController;
use Illuminate\Support\Facades\Route;

// Settings screens: GET reads the stored values, PUT replaces them.
// SMS templates are PUT-updated one row at a time.

Route::get('otp/login-settings', [OtpController::class, 'loginShow'])->name('otp.login.show');
Route::put('otp/login-settings', [OtpController::class, 'loginUpdate'])->name('otp.login.update');
Route::get('otp/configuration', [OtpController::class, 'configurationShow'])->name('otp.configuration.show');
Route::put('otp/configuration', [OtpController::class, 'configurationUpdate'])->name('otp.configuration.update');

Route::prefix('otp/sms-templates')->name('otp.templates.')->group(function () {
    Route::get('/', [OtpController::class, 'templatesIndex'])->name('index');
    Route::get('{template}', [OtpController::class, 'templatesShow'])->name('show');
    Route::put('{template}', [OtpController::class, 'templatesUpdate'])->name('update');
});
