<?php

use App\Enums\UserType;
use App\Http\Controllers\Admin\SellerController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

// {seller} is a user id, but only a seller's: a customer's or an admin's id is a 404,
// so no screen or action here can ever touch a non-seller account.
Route::bind('seller', fn (string $id) => User::where('user_type', UserType::Seller)->whereKey($id)->firstOrFail());

Route::controller(SellerController::class)
    ->prefix('sellers')
    ->name('sellers.')
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('{seller}', 'show')->name('show');
        Route::post('{seller}/verification', 'verification')->name('verification');
        Route::post('{seller}/toggle/{field}', 'toggle')
            ->whereIn('field', ['banned'])
            ->name('toggle');
        Route::delete('{seller}', 'destroy')->name('destroy');
    });
