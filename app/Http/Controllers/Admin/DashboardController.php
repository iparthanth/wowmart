<?php

namespace App\Http\Controllers\Admin;

use App\Enums\DeliveryStatus;
use App\Enums\PaymentStatus;
use App\Enums\UserType;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    private const CHART_DAYS = 14;

    public function index(): View
    {
        return view('admin.dashboard', [
            'queue' => $this->workQueue(),
            'totals' => $this->totals(),
            'revenue' => $this->dailyRevenue(),
            'recentOrders' => Order::with('customer')->latest('id')->limit(8)->get(),
        ]);
    }

    /**
     * What needs doing right now, in the order an operator would tackle it.
     * Every entry links to the filtered list where it gets fixed, so the
     * dashboard is a to-do list rather than a wall of numbers.
     *
     * @return list<array{label:string, count:int, hint:string, route:string, params:array}>
     */
    private function workQueue(): array
    {
        return [
            [
                'label' => 'Pending orders',
                'count' => Order::where('delivery_status', DeliveryStatus::Pending)->count(),
                'hint' => 'waiting to be confirmed',
                'route' => 'admin.orders.index', 'params' => ['delivery_status' => 'pending'],
            ],
            [
                'label' => 'Unpaid orders',
                'count' => Order::where('payment_status', PaymentStatus::Unpaid)
                    ->where('delivery_status', '!=', DeliveryStatus::Cancelled)->count(),
                'hint' => 'cash still to collect',
                'route' => 'admin.orders.index', 'params' => ['payment_status' => 'unpaid'],
            ],
            [
                'label' => 'Out of stock',
                'count' => Product::where('current_stock', '<=', 0)->count(),
                'hint' => 'customers cannot buy these',
                'route' => 'admin.products.index', 'params' => ['stock' => 'out'],
            ],
            [
                'label' => 'Low on stock',
                'count' => Product::where('current_stock', '>', 0)
                    ->whereColumn('current_stock', '<=', 'low_stock_quantity')->count(),
                'hint' => 'at or below reorder level',
                'route' => 'admin.products.index', 'params' => ['stock' => 'low'],
            ],
            [
                'label' => 'Missing images',
                'count' => Product::whereNull('thumbnail_img')->count(),
                'hint' => 'showing a placeholder',
                'route' => 'admin.products.index', 'params' => ['image' => 'missing'],
            ],
        ];
    }

    private function totals(): array
    {
        return [
            'products' => Product::count(),
            'inHouse' => Product::inHouse()->count(),
            'customers' => User::where('user_type', UserType::Customer)->count(),
            'sellers' => User::where('user_type', UserType::Seller)->count(),
            // Cancelled orders never became money, so they are left out.
            'orderValue' => (float) Order::where('delivery_status', '!=', DeliveryStatus::Cancelled)->sum('grand_total'),
        ];
    }

    /**
     * Order value per day for the last CHART_DAYS days, today included, with
     * empty days filled in as zero so the chart never skips a date.
     *
     * @return list<array{date:\Illuminate\Support\Carbon, total:float}>
     */
    private function dailyRevenue(): array
    {
        $from = today()->subDays(self::CHART_DAYS - 1);

        $sums = Order::where('delivery_status', '!=', DeliveryStatus::Cancelled)
            ->where('created_at', '>=', $from)
            ->selectRaw('DATE(created_at) as day, SUM(grand_total) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        return collect(range(0, self::CHART_DAYS - 1))
            ->map(function (int $i) use ($from, $sums) {
                $date = $from->copy()->addDays($i);

                return ['date' => $date, 'total' => (float) ($sums[$date->toDateString()] ?? 0)];
            })
            ->all();
    }
}
