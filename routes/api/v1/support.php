<?php

use App\Http\Controllers\Api\V1\SupportController;
use Illuminate\Support\Facades\Route;

// Ticket replies and status are actions: POST /tickets/{id}/reply|status.
// PUT replaces the record it names (subject / status / the contact's fields).
// Every route binds its action explicitly because the controller groups its
// methods per section (ticketsIndex, contactsShow, ...) instead of the plain
// resource names.

Route::prefix('tickets')->name('tickets.')->group(function () {
    Route::get('/', [SupportController::class, 'ticketsIndex'])->name('index');
    Route::post('/', [SupportController::class, 'ticketsStore'])->name('store');
    Route::get('{ticket}', [SupportController::class, 'ticketsShow'])->name('show');
    Route::put('{ticket}', [SupportController::class, 'ticketsUpdate'])->name('update');
    Route::delete('{ticket}', [SupportController::class, 'ticketsDestroy'])->name('destroy');
    Route::post('{ticket}/reply', [SupportController::class, 'ticketsReply'])->name('reply');
    Route::post('{ticket}/status', [SupportController::class, 'ticketsUpdate'])->name('status');
});

Route::prefix('contacts')->name('contacts.')->group(function () {
    Route::get('/', [SupportController::class, 'contactsIndex'])->name('index');
    Route::post('/', [SupportController::class, 'contactsStore'])->name('store');
    Route::get('{contact}', [SupportController::class, 'contactsShow'])->name('show');
    Route::put('{contact}', [SupportController::class, 'contactsUpdate'])->name('update');
    Route::delete('{contact}', [SupportController::class, 'contactsDestroy'])->name('destroy');
});

Route::prefix('product-queries')->name('queries.')->group(function () {
    Route::get('/', [SupportController::class, 'queriesIndex'])->name('index');
    Route::get('{query}', [SupportController::class, 'queriesShow'])->name('show');
    Route::put('{query}', [SupportController::class, 'queriesUpdate'])->name('update');
    Route::delete('{query}', [SupportController::class, 'queriesDestroy'])->name('destroy');
});

Route::prefix('conversations')->name('conversations.')->group(function () {
    Route::get('/', [SupportController::class, 'conversationsIndex'])->name('index');
    Route::get('{conversation}', [SupportController::class, 'conversationsShow'])->name('show');
    Route::put('{conversation}', [SupportController::class, 'conversationsUpdate'])->name('update');
    Route::delete('{conversation}', [SupportController::class, 'conversationsDestroy'])->name('destroy');
    Route::post('{conversation}/reply', [SupportController::class, 'conversationsReply'])->name('reply');
});
