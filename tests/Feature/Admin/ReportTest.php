<?php

namespace Tests\Feature\Admin;

use App\Enums\DeliveryStatus;
use App\Models\Category;
use App\Models\CommissionLog;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\SearchLog;
use App\Models\Shop;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::where('email', 'admin@wowmart.test')->firstOrFail();
    }

    private function report(string $name, array $query = [])
    {
        return $this->actingAs($this->admin)->get(route("admin.reports.{$name}", $query))->assertOk();
    }

    /** Seeded products in insertion order; index 5 is the sold-out one, 9 and 14 are low. */
    private function product(int $index): Product
    {
        return Product::orderBy('id')->skip($index)->firstOrFail();
    }

    /** A one-line order placed at $at, bypassing the storefront. */
    private function sale(Product $product, int $qty, string $at, DeliveryStatus $status = DeliveryStatus::Delivered): Order
    {
        $order = new Order([
            'user_id' => User::where('email', 'rafi@customers.wowmart.test')->value('id'),
            'shipping_address' => ['name' => 'Test'],
            'payment_type' => 'cash_on_delivery',
        ]);
        $order->forceFill(['code' => uniqid('T'), 'delivery_status' => $status, 'created_at' => Carbon::parse($at), 'updated_at' => Carbon::parse($at)])->save();
        $order->details()->create([
            'seller_id' => $product->added_by === 'seller' ? $product->user_id : null,
            'product_id' => $product->id,
            'price' => $product->unit_price,
            'quantity' => $qty,
        ]);

        return $order;
    }

    /** Expected figures computed independently of ReportService, from Eloquent. */
    private function soldLines(): Collection
    {
        return OrderDetail::with(['order', 'product'])->get()
            ->reject(fn (OrderDetail $d) => $d->order->delivery_status === DeliveryStatus::Cancelled);
    }

    // ---------------------------------------------------------------- stock

    public function test_stock_lists_every_product_lowest_first_with_totals(): void
    {
        $response = $this->report('stock');
        $rows = $response->viewData('rows');
        $all = Product::all();

        $this->assertSame(42, $rows->total());
        $this->assertCount(25, $rows->items());
        $this->assertSame($this->product(5)->id, $rows->items()[0]['id']);
        $this->assertSame(0, $rows->items()[0]['current_stock']);
        $this->assertSame('out', $rows->items()[0]['state']);
        $this->assertSame(['low', 'low'], [$rows->items()[1]['state'], $rows->items()[2]['state']]);
        $this->assertSame(array_values(collect($rows->items())->pluck('current_stock')->sort()->all()), collect($rows->items())->pluck('current_stock')->all());

        $totals = $response->viewData('totals');
        $this->assertSame(42, $totals['products']);
        $this->assertSame((int) $all->sum('current_stock'), $totals['units']);
        $this->assertEqualsWithDelta(round($all->sum(fn ($p) => $p->unit_price * $p->current_stock), 2), $totals['stock_value'], 0.001);

        $row = collect($rows->items())->firstWhere('state', 'ok');
        $this->assertEqualsWithDelta(round(Product::find($row['id'])->unit_price * $row['current_stock'], 2), $row['stock_value'], 0.001);

        $response->assertSee('Sold out')->assertSee('Download CSV')->assertSee('Total, 42 products');
    }

    public function test_stock_filters_by_state_search_and_category(): void
    {
        $this->assertSame(1, $this->report('stock', ['state' => 'out'])->viewData('rows')->total());
        $this->assertSame(2, $this->report('stock', ['state' => 'low'])->viewData('rows')->total());
        $this->assertSame(39, $this->report('stock', ['state' => 'ok'])->viewData('rows')->total());

        $low = $this->report('stock', ['state' => 'low'])->viewData('totals');
        $this->assertSame(['products' => 2, 'units' => 6], ['products' => $low['products'], 'units' => $low['units']]);

        $bySku = $this->report('stock', ['search' => 'WM-00006'])->viewData('rows');
        $this->assertSame([$this->product(5)->id], collect($bySku->items())->pluck('id')->all());

        $category = Category::whereHas('products')->withCount('products')->firstOrFail();
        $this->assertSame($category->products_count, $this->report('stock', ['category' => $category->id])->viewData('rows')->total());

        // Unknown or malformed values are ignored, not rejected.
        $this->assertSame(42, $this->report('stock', ['state' => 'nope', 'category' => ['x'], 'search' => ['y']])->viewData('rows')->total());
    }

    public function test_negative_stock_counts_as_zero_in_the_totals(): void
    {
        $p = $this->product(20);
        $p->forceFill(['current_stock' => -4])->save();

        $totals = $this->report('stock', ['search' => $p->sku])->viewData('totals');

        $this->assertSame(['products' => 1, 'units' => 0, 'stock_value' => 0.0], $totals);
    }

    // -------------------------------------------------------- in-house sales

    public function test_in_house_sales_match_non_cancelled_order_lines(): void
    {
        $lines = $this->soldLines()->filter(fn (OrderDetail $d) => $d->product->added_by === 'admin');
        $byProduct = $lines->groupBy('product_id');
        $this->assertGreaterThan(5, $byProduct->count()); // not vacuous

        $response = $this->report('in-house-sales');
        $rows = $response->viewData('rows');
        $totals = $response->viewData('totals');

        $this->assertSame($byProduct->count(), $rows->count());
        $this->assertSame((int) $lines->sum('quantity'), $totals['units']);
        $this->assertEqualsWithDelta(round($lines->sum(fn ($d) => $d->price * $d->quantity), 2), $totals['revenue'], 0.001);
        $this->assertSame($byProduct->count(), $totals['products']);

        foreach ($rows as $row) {
            $this->assertSame((int) $byProduct[$row['id']]->sum('quantity'), $row['units']);
            $this->assertEqualsWithDelta(round($byProduct[$row['id']]->sum(fn ($d) => $d->price * $d->quantity), 2), $row['revenue'], 0.001);
        }

        // Sorted by units, most first.
        $this->assertSame($rows->pluck('units')->sortDesc()->values()->all(), $rows->pluck('units')->all());

        // Product 5 is in-house and was only ever on the seeded cancelled order.
        $this->assertSame(DeliveryStatus::Cancelled, OrderDetail::where('product_id', $this->product(5)->id)->sole()->order->delivery_status);
        $this->assertNotContains($this->product(5)->id, $rows->pluck('id')->all());
    }

    public function test_cancelling_an_order_removes_its_lines_from_the_sales_reports(): void
    {
        $inHouse = $this->product(0);
        $before = $this->report('in-house-sales')->viewData('totals');

        $order = $this->sale($inHouse, 7, 'today 09:00', DeliveryStatus::Cancelled);
        $this->assertSame($before, $this->report('in-house-sales')->viewData('totals'));

        $order->forceFill(['delivery_status' => DeliveryStatus::Delivered])->save();
        $after = $this->report('in-house-sales')->viewData('totals');
        $this->assertSame($before['units'] + 7, $after['units']);
        $this->assertEqualsWithDelta($before['revenue'] + 7 * $inHouse->unit_price, $after['revenue'], 0.001);
    }

    public function test_sales_date_range_is_inclusive_whole_days_in_dhaka(): void
    {
        $p = $this->product(1);
        $this->sale($p, 1, '2026-01-09 23:59:59');   // day before: out
        $this->sale($p, 2, '2026-01-10 00:00:00');   // first second: in
        $this->sale($p, 4, '2026-01-12 23:59:59');   // last second: in
        $this->sale($p, 8, '2026-01-13 00:00:00');   // day after: out

        $range = ['date_from' => '2026-01-10', 'date_to' => '2026-01-12'];
        $rows = $this->report('in-house-sales', $range)->viewData('rows');

        $this->assertSame([['id' => $p->id, 'units' => 6]], $rows->map(fn ($r) => ['id' => $r['id'], 'units' => $r['units']])->all());

        // A reversed range is swapped rather than coming back empty.
        $this->assertSame(6, $this->report('in-house-sales', ['date_from' => '2026-01-12', 'date_to' => '2026-01-10'])->viewData('totals')['units']);
    }

    public function test_sales_default_to_the_last_30_days_including_today(): void
    {
        $p = $this->product(1);
        $seeded = $this->report('in-house-sales')->viewData('totals')['units'];

        $this->sale($p, 3, now()->subDays(29)->startOfDay()->toDateTimeString());   // in
        $this->sale($p, 5, now()->subDays(30)->endOfDay()->toDateTimeString());     // out

        $response = $this->report('in-house-sales', ['date_from' => '2026-02-31']); // invalid: ignored
        $this->assertSame($seeded + 3, $response->viewData('totals')['units']);
        $this->assertSame(['date_from' => today()->subDays(29)->toDateString(), 'date_to' => today()->toDateString()], $response->viewData('filters'));
    }

    // ---------------------------------------------------------- seller sales

    public function test_seller_sales_have_one_row_per_shop_matching_order_lines(): void
    {
        $lines = $this->soldLines()->whereNotNull('seller_id');
        $this->assertGreaterThan(0, $lines->count());

        $response = $this->report('seller-sales');
        $rows = $response->viewData('rows');
        $totals = $response->viewData('totals');

        $this->assertSame(5, $rows->count());
        $this->assertSame(5, $totals['shops']);
        $this->assertSame($lines->pluck('order_id')->unique()->count(), $totals['orders']);
        $this->assertSame((int) $lines->sum('quantity'), $totals['units']);
        $this->assertEqualsWithDelta(round($lines->sum(fn ($d) => $d->price * $d->quantity), 2), $totals['revenue'], 0.001);

        foreach (Shop::all() as $shop) {
            $mine = $lines->where('seller_id', $shop->user_id);
            $row = $rows->firstWhere('id', $shop->id);
            $this->assertSame($mine->pluck('order_id')->unique()->count(), $row['orders'], $shop->name);
            $this->assertSame((int) $mine->sum('quantity'), $row['units'], $shop->name);
        }

        $this->assertSame($rows->pluck('revenue')->sortDesc()->values()->all(), $rows->pluck('revenue')->all());
    }

    public function test_seller_orders_are_distinct_and_cancelled_orders_are_left_out(): void
    {
        $sellerProduct = $this->product(3);
        $shop = Shop::where('user_id', $sellerProduct->user_id)->sole();
        $before = $this->report('seller-sales')->viewData('rows')->firstWhere('id', $shop->id);

        // Two lines of one order count as one order.
        $order = $this->sale($sellerProduct, 2, 'today 08:00');
        $order->details()->create(['seller_id' => $sellerProduct->user_id, 'product_id' => $sellerProduct->id, 'price' => 10, 'quantity' => 1]);
        $this->sale($sellerProduct, 9, 'today 08:30', DeliveryStatus::Cancelled);

        $after = $this->report('seller-sales')->viewData('rows')->firstWhere('id', $shop->id);
        $this->assertSame($before['orders'] + 1, $after['orders']);
        $this->assertSame($before['units'] + 3, $after['units']);
        $this->assertEqualsWithDelta($before['revenue'] + 2 * $sellerProduct->unit_price + 10, $after['revenue'], 0.001);
    }

    // -------------------------------------------------------------- wishlist

    public function test_wishlist_lists_every_product_with_zeros_included(): void
    {
        $customers = User::where('email', 'like', '%@customers.wowmart.test')->orderBy('id')->get();
        [$a, $b] = [$this->product(2), $this->product(8)];
        foreach ($customers as $c) {
            Wishlist::create(['user_id' => $c->id, 'product_id' => $a->id]);
        }
        Wishlist::create(['user_id' => $customers[0]->id, 'product_id' => $b->id]);

        $response = $this->report('wishlist');
        $rows = $response->viewData('rows');

        // Every product gets a row, like the reference panel; most wished first.
        $this->assertSame(42, $rows->count());
        $this->assertSame([[$a->id, $customers->count()], [$b->id, 1]], $rows->take(2)->map(fn ($r) => [$r['id'], $r['wishlists']])->all());
        $this->assertContains(0, $rows->pluck('wishlists')->unique()->all());

        $this->assertSame(['products' => 42, 'wishlists' => $customers->count() + 1], $response->viewData('totals'));

        // Thumbnails resolve to URLs in one batch; a product without an image stays null.
        $imaged = $rows->firstWhere('id', $this->product(0)->id);
        $this->assertStringContainsString('uploads/catalog/', $imaged['thumb']);
        $this->assertNull($rows->firstWhere('id', $this->product(10)->id)['thumb']);

        // Filtering by category keeps every product of that category, zeros included.
        $inCategory = $this->report('wishlist', ['category' => $a->category_id]);
        $categoryRows = $inCategory->viewData('rows');
        $this->assertSame($a->id, $categoryRows->first()['id']);
        $this->assertSame(Category::whereKey($a->category_id)->withCount('products')->firstOrFail()->products_count, $categoryRows->count());
        $this->assertSame($categoryRows->sum('wishlists'), $inCategory->viewData('totals')['wishlists']);
    }

    // ---------------------------------------------------------- user searches

    public function test_user_searches_aggregate_terms_most_searched_first(): void
    {
        $response = $this->report('user-searches');
        $rows = $response->viewData('rows');
        $totals = $response->viewData('totals');

        // The seeder logs each term a known number of times.
        $this->assertSame(12, $totals['terms']);
        $this->assertSame(SearchLog::count(), $totals['searches']);
        $this->assertSame(72, $totals['searches']);

        $this->assertSame('drone kit', $rows[0]['query']);
        $this->assertSame(14, $rows[0]['searches']);
        $this->assertSame($rows->pluck('searches')->sortDesc()->values()->all(), $rows->pluck('searches')->all());

        $filtered = $this->report('user-searches', ['search' => 'servo']);
        $this->assertSame([['servo motor', 9]], $filtered->viewData('rows')->map(fn ($r) => [$r['query'], $r['searches']])->all());
    }

    // ----------------------------------------------------- commission history

    public function test_commission_history_rows_match_paid_seller_orders(): void
    {
        $rate = 5.0; // ReportSeeder's commission rate
        // Sellers ride on the order lines, so the commission unit is one seller
        // inside one paid order.
        $pairs = OrderDetail::query()
            ->join('orders as o', 'o.id', '=', 'order_details.order_id')
            ->where('o.payment_status', 'paid')
            ->whereNotNull('order_details.seller_id')
            ->get(['order_details.order_id', 'order_details.seller_id', 'order_details.price', 'order_details.quantity'])
            ->groupBy(fn ($d) => $d->order_id.'-'.$d->seller_id);
        $this->assertGreaterThan(0, $pairs->count());

        $response = $this->report('commission-history');
        $rows = $response->viewData('rows');
        $totals = $response->viewData('totals');

        $this->assertSame($pairs->count(), $rows->count());
        $this->assertSame($pairs->count(), $totals['orders']);

        $expectedCommission = round($pairs->sum(fn ($lines) => $lines->sum(fn ($d) => $d->price * $d->quantity)) * $rate / 100, 2);
        $expectedEarning = round($pairs->sum(fn ($lines) => $lines->sum(fn ($d) => $d->price * $d->quantity)) * (100 - $rate) / 100, 2);
        $this->assertEqualsWithDelta($expectedCommission, $totals['admin_commission'], 0.01);
        $this->assertEqualsWithDelta($expectedEarning, $totals['seller_earning'], 0.01);

        // Every stored commission row appears with its own figures (one order
        // can hold two sellers, so compare as multisets, not per order code).
        $this->assertEqualsCanonicalizing(
            CommissionLog::pluck('admin_commission')->map(fn ($v) => round((float) $v, 2))->all(),
            $rows->pluck('admin_commission')->all(),
        );
        $this->assertEqualsCanonicalizing(
            CommissionLog::pluck('seller_earning')->map(fn ($v) => round((float) $v, 2))->all(),
            $rows->pluck('seller_earning')->all(),
        );

        // Newest first.
        $dates = $rows->pluck('created_at')->all();
        $this->assertSame($dates, array_values(collect($dates)->sortDesc()->values()->all()));
    }

    // ------------------------------------------------------- wallet recharges

    public function test_wallet_recharges_list_with_an_approval_filter(): void
    {
        $response = $this->report('wallet-recharges');
        $rows = $response->viewData('rows');

        $this->assertSame(5, $rows->count()); // all from ReportSeeder
        $this->assertSame(455.0, $response->viewData('totals')['amount']);

        // Pending first, then newest first.
        $this->assertSame('pending', $rows[0]['approval']);

        $pending = $this->report('wallet-recharges', ['approval' => 'pending'])->viewData('rows');
        $this->assertCount(2, $pending);
        $this->assertSame(275.0, $pending->sum('amount'));

        // An unknown state shows everything rather than nothing.
        $this->assertSame(5, $this->report('wallet-recharges', ['approval' => 'maybe'])->viewData('rows')->count());
    }

    // ---------------------------------------------------------------- earning

    public function test_earning_report_has_one_row_per_shop_with_zeros(): void
    {
        $response = $this->report('earning');
        $rows = $response->viewData('rows');
        $totals = $response->viewData('totals');

        // Every shop gets a row; only shops with paid orders move the totals.
        $this->assertSame(5, $rows->count());
        $this->assertSame(CommissionLog::distinct()->count('seller_id'), $totals['sellers']);

        $history = $this->report('commission-history')->viewData('totals');
        $this->assertEqualsWithDelta($history['admin_commission'], $totals['admin_commission'], 0.001);
        $this->assertEqualsWithDelta($history['seller_earning'], $totals['seller_earning'], 0.001);

        // Sorted by what the shop earned, most first; zeros at the bottom.
        $earnings = $rows->pluck('seller_earning')->all();
        $this->assertSame(collect($earnings)->sortDesc()->values()->all(), $earnings);
    }

    // ------------------------------------------------------------------- CSV

    public function test_csv_download_has_bom_header_plain_money_and_neutralised_formulas(): void
    {
        $p = $this->product(0);
        $p->forceFill(['name' => '=HYPERLINK(1)', 'current_stock' => 0])->save();

        $response = $this->actingAs($this->admin)->get(route('admin.reports.stock', ['format' => 'csv', 'search' => 'HYPERLINK']));
        $response->assertOk()->assertDownload('wowmart-stock-'.today()->toDateString().'.csv');
        $this->assertStringStartsWith('text/csv', $response->headers->get('Content-Type'));

        $csv = $response->streamedContent();
        $this->assertStringStartsWith("\xEF\xBB\xBF", $csv);
        $lines = array_map(fn ($l) => str_getcsv($l, escape: ''), explode("\n", trim(substr($csv, 3))));

        $this->assertSame(['Product', 'SKU', 'Category', 'Current stock', 'Low-stock level', 'State', 'Unit price', 'Stock value'], $lines[0]);
        $this->assertCount(2, $lines);
        $this->assertSame("'=HYPERLINK(1)", $lines[1][0]);
        $this->assertSame('Sold out', $lines[1][5]);
        $this->assertSame(number_format($p->unit_price, 2, '.', ''), $lines[1][6]);
        $this->assertSame('0.00', $lines[1][7]);
    }

    public function test_every_report_downloads_as_csv(): void
    {
        $headers = [
            'in-house-sales' => ['Product', 'SKU', 'Units sold', 'Revenue'],
            'seller-sales' => ['Shop', 'Seller', 'Orders', 'Units sold', 'Revenue'],
            'wishlist' => ['Product', 'SKU', 'Category', 'Wishlists'],
        ];

        foreach ($headers as $report => $expected) {
            $response = $this->actingAs($this->admin)->get(route("admin.reports.{$report}", ['format' => 'csv']));
            $response->assertOk()->assertDownload("wowmart-{$report}-".today()->toDateString().'.csv');
            $lines = explode("\n", trim(substr($response->streamedContent(), 3)));
            $this->assertSame($expected, str_getcsv($lines[0], escape: ''), $report);
        }

        // Seller sales rows: one per shop, revenue a plain number.
        $lines = explode("\n", trim(substr($this->actingAs($this->admin)->get(route('admin.reports.seller-sales', ['format' => 'csv']))->streamedContent(), 3)));
        $this->assertCount(6, $lines);
        $this->assertMatchesRegularExpression('/^\d+\.\d{2}$/', str_getcsv($lines[1], escape: '')[4]);
    }

    public function test_every_screen_renders(): void
    {
        $this->report('in-house-sales')->assertSee('Cancelled orders are not counted')->assertSee('Download CSV');
        $this->report('seller-sales')->assertSee('Total, 5 shops');
        // The wishlist report always lists products, so the page shows rows.
        $this->report('wishlist')->assertSee($this->product(0)->name)->assertSee('No image');
        $this->report('in-house-sales', ['date_from' => '2020-01-01', 'date_to' => '2020-01-02'])->assertSee('No in-house sales in this range');
    }
}
