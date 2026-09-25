<?php

use App\Http\Controllers\Api\V1\CustomerController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

// {customer} only ever resolves a customer: an admin's or seller's id is a 404 here.
Route::bind('customer', fn (string $id) => User::customers()->findOrFail($id));

// No impersonation endpoint, on purpose - see routes/admin/customers.php.

// Update is PUT only: CustomerRequest is a full replacement, so a partial PATCH
// would clear the email or phone it did not send.
Route::apiResource('customers', CustomerController::class)->except('update');
Route::put('customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
Route::patch('customers/{customer}/flags', [CustomerController::class, 'flags'])->name('customers.flags');
