<?php

use App\Http\Controllers\Admin\OrderController;
use Illuminate\Support\Facades\Route;

/*
 * Paths mirror the reference panel (/admin/all_orders).
 *
 * Orders are bound by their CODE, not by the reference's encrypt()ed id: every
 * admin may see every order, so hiding ids protects nothing here, and the code
 * is what staff and customers read out on the phone.
 *
 * There are deliberately no create, edit or delete routes. Orders come from
 * the storefront, and deleting one would destroy a financial record; the admin
 * only moves an order through its delivery and payment states.
 */

Route::controller(OrderController::class)
    ->prefix('all_orders')
    ->name('orders.')
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('{order:code}', 'show')->name('show');
        Route::post('{order:code}/delivery-status', 'deliveryStatus')->name('delivery-status');
        Route::post('{order:code}/payment-status', 'paymentStatus')->name('payment-status');
        Route::post('{order:code}/assign-delivery-boy', 'assignDeliveryBoy')->name('assign-delivery-boy');
        Route::post('{order:code}/unassign-delivery-boy', 'unassignDeliveryBoy')->name('unassign-delivery-boy');
    });

// The reference panel's Sales lists: fixed-filter views over the same orders.
// Static paths come before "{order:code}" only in their own prefix groups, so
// they never collide with the all_orders show route.
Route::controller(OrderController::class)
    ->name('orders.')
    ->group(function () {
        Route::get('inhouse-orders', 'inhouse')->name('inhouse');
        Route::get('seller_orders', 'sellerOrders')->name('seller');
        Route::get('orders_by_pickup_point', 'byPickupPoint')->name('pickup');
        Route::get('unpaid_orders', 'unpaid')->name('unpaid');
    });
