<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use Illuminate\Support\Facades\Route;

/*
 * This application is the WowMart admin panel only - it has no storefront routes.
 *
 * Sign-in is at /login and the panel lives under /admin, and neither is linked
 * from any public page. Obscurity is not the control, though: the `admin`
 * middleware on the group is, and tests/Feature/Admin/AdminRoutesTest proves
 * every route in the group carries it.
 */

Route::redirect('/', '/admin');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('admin.login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('admin.logout');

Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // One file per module (routes/admin/products.php, orders.php, ...), each
        // inheriting this group's prefix, name and middleware.
        foreach (glob(__DIR__.'/admin/*.php') as $module) {
            require $module;
        }
    });
