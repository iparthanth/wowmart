<?php

namespace App\Providers;

use App\Enums\DeliveryStatus;
use App\Models\Order;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Plain CSS, no Tailwind: Bootstrap 5 pagination markup gives predictable
        // .pagination / .page-link classes to style.
        Paginator::useBootstrapFive();

        // The sidebar's "needs attention" count. Computed once per page render
        // here, rather than as a query hidden inside the layout template.
        View::composer('admin.layouts.app', function ($view) {
            $view->with('pendingOrders', Order::where('delivery_status', DeliveryStatus::Pending)->count());
        });
    }
}
