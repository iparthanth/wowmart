<?php

use App\Http\Controllers\Api\V1\RefundController;
use Illuminate\Support\Facades\Route;

// Decide-only, like the panel (see routes/admin/refunds.php).

Route::controller(RefundController::class)->group(function () {
    Route::get('refund-requests', 'index')->name('refund-requests.index');
    Route::get('refund-requests/{refund}', 'show')->name('refund-requests.show');
    Route::patch('refund-requests/{refund}/status', 'status')->name('refund-requests.status');
    Route::get('refund-settings', 'settings')->name('refund-settings.show');
    Route::put('refund-settings', 'updateSettings')->name('refund-settings.update');
});
