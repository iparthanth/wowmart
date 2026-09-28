<?php

use App\Http\Controllers\Admin\SupportController;
use Illuminate\Support\Facades\Route;

// The support desk: tickets with threads, contact messages, product queries
// and buyer-seller conversations. Status changes are POST (not PUT) because
// they are actions on the record, not full replacements.

Route::controller(SupportController::class)
    ->prefix('support')
    ->name('support.')
    ->group(function () {
        Route::get('tickets', 'tickets')->name('tickets');
        Route::post('tickets', 'storeTicket')->name('tickets.store');
        Route::get('tickets/{ticket}', 'showTicket')->name('tickets.show');
        Route::post('tickets/{ticket}/reply', 'replyTicket')->name('tickets.reply');
        Route::post('tickets/{ticket}/status', 'ticketStatus')->name('tickets.status');

        Route::get('contacts', 'contacts')->name('contacts');
        Route::post('contacts/{contact}/reply', 'replyContact')->name('contacts.reply');
        Route::delete('contacts/{contact}', 'destroyContact')->name('contacts.destroy');

        Route::get('product-queries', 'queries')->name('queries');
        Route::post('product-queries/{query}/reply', 'replyQuery')->name('queries.reply');
        Route::delete('product-queries/{query}', 'destroyQuery')->name('queries.destroy');

        Route::get('conversations', 'conversations')->name('conversations');
        Route::get('conversations/{conversation}', 'showConversation')->name('conversations.show');
        Route::post('conversations/{conversation}/reply', 'replyConversation')->name('conversations.reply');
        Route::post('conversations/{conversation}/status', 'conversationStatus')->name('conversations.status');
    });
