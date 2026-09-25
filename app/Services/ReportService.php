<?php

namespace App\Services;

use App\Enums\DeliveryStatus;
use App\Models\Order;
use App\Models\Product;
use Closure;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Every report query, shared by the panel, its CSV download and the API.
 *
 * Each report returns the same shape:
 *   filters - the effective filters (defaults applied), echoed back to the caller
 *   query   - the row query, aggregated in SQL (one query, no per-row lookups)
 *   map     - turns a query row into a typed array (ints, money as float)
 *   totals  - one aggregate query over the same filtered rows
 *
 * Read-only: nothing here writes, so there are no transactions.
 */
class ReportService
{
    public const REPORTS = ['stock', 'in-house-sales', 'seller-sales', 'wishlist'];

    /** Keys holding money, in rows and totals; the API renders them as 2-decimal strings. */
    public const MONEY = ['unit_price', 'stock_value', 'revenue'];

    public const STATES = ['out' => 'Sold out', 'low' => 'Low', 'ok' => 'In stock'];

    /** @return array{filters: array, query: Builder, map: Closure, totals: array} */
    public function run(string $report, array $in): array
    {
        return match ($report) {
            'stock' => $this->stock($in),
            'in-house-sales' => $this->inHouseSales($in),
            'seller-sales' => $this->sellerSales($in),
            'wishlist' => $this->wishlist($in),
        };
    }

    public function stock(array $in): array
    {
        $f = [
            'category' => $this->category($in),
            'state' => is_string($in['state'] ?? null) && isset(self::STATES[$in['state']]) ? $in['state'] : null,
            'search' => is_string($in['search'] ?? null) ? trim($in['search']) : '',
        ];

        // Eloquent only for whereContains (an Eloquent macro); toBase() below
        // hands back plain rows like the other reports.
        $base = fn () => Product::from('products as p')
            ->when($f['category'], $this->inCategory(...))
            ->when($f['search'] !== '', fn (EloquentBuilder $q) => $q->whereContains(['p.name', 'p.sku'], $f['search']))
            ->when($f['state'] === 'out', fn (EloquentBuilder $q) => $q->where('p.current_stock', '<=', 0))
            ->when($f['state'] === 'low', fn (EloquentBuilder $q) => $q->where('p.current_stock', '>', 0)->whereColumn('p.current_stock', '<=', 'p.low_stock_quantity'))
            ->when($f['state'] === 'ok', fn (EloquentBuilder $q) => $q->whereColumn('p.current_stock', '>', 'p.low_stock_quantity'))
            ->toBase();

        // current_stock is signed (a concurrent sale can overshoot); stock you do
        // not have is worth nothing, so negatives count as zero in value and units.
        $onHand = 'case when p.current_stock > 0 then p.current_stock else 0 end';

        $totals = $base()->selectRaw("count(*) as products, coalesce(sum($onHand), 0) as units, coalesce(sum(p.unit_price * $onHand), 0) as stock_value")->first();

        return [
            'filters' => $f,
            'query' => $base()
                ->leftJoin('categories as c', 'c.id', '=', 'p.category_id')
                ->select('p.id', 'p.name', 'p.sku', 'c.name as category', 'p.current_stock', 'p.low_stock_quantity', 'p.unit_price')
                ->selectRaw("p.unit_price * $onHand as stock_value")
                ->orderBy('p.current_stock')
                ->orderBy('p.id'),
            'map' => fn (object $r) => [
                'id' => (int) $r->id,
                'name' => $r->name,
                'sku' => $r->sku,
                'category' => $r->category,
                'current_stock' => (int) $r->current_stock,
                'low_stock_quantity' => (int) $r->low_stock_quantity,
                'state' => $state = match (true) {
                    $r->current_stock <= 0 => 'out',
                    $r->current_stock <= $r->low_stock_quantity => 'low',
                    default => 'ok',
                },
                'state_label' => self::STATES[$state],
                'unit_price' => round((float) $r->unit_price, 2),
                'stock_value' => round((float) $r->stock_value, 2),
            ],
            'totals' => [
                'products' => (int) $totals->products,
                'units' => (int) $totals->units,
                'stock_value' => round((float) $totals->stock_value, 2),
            ],
        ];
    }

    public function inHouseSales(array $in): array
    {
        $f = $this->dateRange($in);
        $base = fn () => $this->soldLines($f)
            ->join('products as p', 'p.id', '=', 'd.product_id')
            ->where('p.added_by', 'admin');

        $totals = $base()->selectRaw('count(distinct p.id) as products, coalesce(sum(d.quantity), 0) as units, coalesce(sum(d.price * d.quantity), 0) as revenue')->first();

        return [
            'filters' => $f,
            // Inner joins leave out products with no sales in the range.
            'query' => $base()
                ->groupBy('p.id', 'p.name', 'p.sku')
                ->select('p.id', 'p.name', 'p.sku')
                ->selectRaw('sum(d.quantity) as units, sum(d.price * d.quantity) as revenue')
                ->orderByDesc('units')
                ->orderBy('p.id'),
            'map' => fn (object $r) => [
                'id' => (int) $r->id,
                'name' => $r->name,
                'sku' => $r->sku,
                'units' => (int) $r->units,
                'revenue' => round((float) $r->revenue, 2),
            ],
            'totals' => [
                'products' => (int) $totals->products,
                'units' => (int) $totals->units,
                'revenue' => round((float) $totals->revenue, 2),
            ],
        ];
    }

    public function sellerSales(array $in): array
    {
        $f = $this->dateRange($in);
        // Only lines of sellers that have a shop, so the totals add up to the rows.
        $lines = fn () => $this->soldLines($f)->whereIn('d.seller_id', DB::table('shops')->select('user_id'));

        $perSeller = $lines()
            ->groupBy('d.seller_id')
            ->select('d.seller_id')
            ->selectRaw('count(distinct d.order_id) as orders, sum(d.quantity) as units, sum(d.price * d.quantity) as revenue');

        // Total orders is counted directly: one order can hold lines from two
        // shops, so summing the per-shop counts would count it twice.
        $totals = $lines()->selectRaw('count(distinct d.order_id) as orders, coalesce(sum(d.quantity), 0) as units, coalesce(sum(d.price * d.quantity), 0) as revenue')->first();

        return [
            'filters' => $f,
            // Every shop gets a row, so a seller with no sales in the range shows as zero.
            'query' => DB::table('shops as s')
                ->join('users as u', 'u.id', '=', 's.user_id')
                ->leftJoinSub($perSeller, 'x', 'x.seller_id', '=', 's.user_id')
                ->select('s.id', 's.name as shop', 'u.name as seller')
                ->selectRaw('coalesce(x.orders, 0) as orders, coalesce(x.units, 0) as units, coalesce(x.revenue, 0) as revenue')
                ->orderByDesc('revenue')
                ->orderBy('s.name')
                ->orderBy('s.id'),
            'map' => fn (object $r) => [
                'id' => (int) $r->id,
                'shop' => $r->shop,
                'seller' => $r->seller,
                'orders' => (int) $r->orders,
                'units' => (int) $r->units,
                'revenue' => round((float) $r->revenue, 2),
            ],
            'totals' => [
                'shops' => DB::table('shops')->count(),
                'orders' => (int) $totals->orders,
                'units' => (int) $totals->units,
                'revenue' => round((float) $totals->revenue, 2),
            ],
        ];
    }

    public function wishlist(array $in): array
    {
        $f = ['category' => $this->category($in)];
        $base = fn () => DB::table('wishlists as w')
            ->join('products as p', 'p.id', '=', 'w.product_id')
            ->when($f['category'], $this->inCategory(...));

        $totals = $base()->selectRaw('count(distinct p.id) as products, count(*) as wishlists')->first();

        return [
            'filters' => $f,
            'query' => $base()
                ->leftJoin('categories as c', 'c.id', '=', 'p.category_id')
                ->groupBy('p.id', 'p.name', 'p.sku', 'c.name')
                ->select('p.id', 'p.name', 'p.sku', 'c.name as category')
                ->selectRaw('count(*) as wishlists')
                ->orderByDesc('wishlists')
                ->orderBy('p.id'),
            'map' => fn (object $r) => [
                'id' => (int) $r->id,
                'name' => $r->name,
                'sku' => $r->sku,
                'category' => $r->category,
                'wishlists' => (int) $r->wishlists,
            ],
            'totals' => [
                'products' => (int) $totals->products,
                'wishlists' => (int) $totals->wishlists,
            ],
        ];
    }

    /** Order lines that count as sales: order not cancelled, placed within the range. */
    private function soldLines(array $f): Builder
    {
        // created_at is stored in the app timezone (Asia/Dhaka), so whole days
        // compare directly; the end bound is exclusive of the next day.
        return DB::table('order_details as d')
            ->join('orders as o', 'o.id', '=', 'd.order_id')
            ->where('o.delivery_status', '!=', DeliveryStatus::Cancelled->value)
            ->where('o.created_at', '>=', Carbon::parse($f['date_from'])->startOfDay())
            ->where('o.created_at', '<', Carbon::parse($f['date_to'])->addDay()->startOfDay());
    }

    /**
     * ?date_from / ?date_to as whole days, inclusive. Invalid or missing values
     * fall back to the last 30 days including today; a reversed range is swapped.
     *
     * @return array{date_from: string, date_to: string}
     */
    private function dateRange(array $in): array
    {
        $given = Order::filtersFrom($in); // the order list's strict Y-m-d validation
        $from = $given['date_from'] ?? today()->subDays(29)->toDateString();
        $to = $given['date_to'] ?? today()->toDateString();

        return $from <= $to ? ['date_from' => $from, 'date_to' => $to] : ['date_from' => $to, 'date_to' => $from];
    }

    private function category(array $in): ?int
    {
        return is_numeric($in['category'] ?? null) && (int) $in['category'] > 0 ? (int) $in['category'] : null;
    }

    /** Any category the product appears in, like the product list's filter. */
    private function inCategory(Builder|EloquentBuilder $q, int $id): Builder|EloquentBuilder
    {
        return $q->whereExists(fn (Builder $e) => $e->selectRaw('1')->from('product_categories as pc')
            ->whereColumn('pc.product_id', 'p.id')->where('pc.category_id', $id));
    }
}
