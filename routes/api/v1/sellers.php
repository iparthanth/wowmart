<?php

use App\Enums\UserType;
use App\Http\Controllers\Api\V1\SellerController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

// Same binding as the panel (routes/admin/sellers.php): only sellers resolve.
// No create or update: sellers sign up on the storefront; the admin only
// verifies, bans or removes them.
Route::bind('seller', fn (string $id) => User::where('user_type', UserType::Seller)->whereKey($id)->firstOrFail());

Route::controller(SellerController::class)
    ->prefix('sellers')
    ->name('sellers.')
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('{seller}', 'show')->name('show');
        Route::patch('{seller}/verification', 'verification')->name('verification');
        Route::patch('{seller}/flags', 'flags')->name('flags');
        Route::delete('{seller}', 'destroy')->name('destroy');
    });
