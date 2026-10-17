<?php

namespace App\Providers;

use App\Enums\DeliveryStatus;
use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
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

        /*
         * ->whereContains(['name', 'email'], $term): any column contains the typed
         * text literally. % and _ match themselves (emails often contain _), and
         * '!' is the escape character because MySQL and SQLite read a backslash
         * differently. The ORs are grouped so they cannot swallow other constraints.
         * Columns may be JSON paths ('shipping_address->phone').
         */
        Builder::macro('whereContains', function (string|array $columns, string $term) {
            $pattern = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $term).'%';

            /** @var Builder $this */
            return $this->where(function (Builder $q) use ($columns, $pattern) {
                foreach ((array) $columns as $column) {
                    $q->orWhereRaw($q->getQuery()->getGrammar()->wrap($column)." like ? escape '!'", [$pattern]);
                }
            });
        });

        // The sidebar's "needs attention" count. Computed once per page render
        // here, rather than as a query hidden inside the layout template.
        View::composer('admin.layouts.app', function ($view) {
            $view->with([
                'pendingOrders' => Order::where('delivery_status', DeliveryStatus::Pending)->count(),
            ]);
        });
    }
}
