<?php

use App\Http\Controllers\Admin\CustomerController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

// {customer} only ever resolves a customer: an admin's or seller's id is a 404 here.
Route::bind('customer', fn (string $id) => User::customers()->findOrFail($id));

/*
 * DELIBERATELY NOT BUILT: the reference panel's "Log in as this customer" link.
 * It is a plain GET that swaps the admin's session for the customer's, so any
 * page that gets an admin to load that URL (an <img>, a link in an email) signs
 * them in as someone else - a session-hijack and CSRF risk. If impersonation is
 * ever needed it should be a POST with CSRF, audited, time-limited, and able to
 * return to the admin session.
 */

Route::controller(CustomerController::class)
    ->prefix('customers')
    ->name('customers.')
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('{customer}', 'show')->name('show');
        Route::get('{customer}/edit', 'edit')->name('edit');
        Route::put('{customer}', 'update')->name('update');
        Route::delete('{customer}', 'destroy')->name('destroy');
        Route::post('{customer}/toggle/{field}', 'toggle')
            ->whereIn('field', ['banned'])
            ->name('toggle');
    });
