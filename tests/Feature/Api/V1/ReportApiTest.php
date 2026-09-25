<?php

namespace Tests\Feature\Api\V1;

use App\Enums\DeliveryStatus;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ReportApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Sanctum::actingAs(User::where('email', 'admin@wowmart.test')->firstOrFail());
    }

    public function test_stock_is_paginated_with_totals_and_filters(): void
    {
        $all = Product::all();

        $this->getJson('/api/v1/reports/stock?per_page=5&state=low')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.total', 2)
            ->assertJsonPath('data.0.state', 'low')
            ->assertJsonPath('data.0.state_label', 'Low')
            ->assertJsonPath('data.0.current_stock', 3)
            ->assertJsonPath('totals.products', 2)
            ->assertJsonPath('totals.units', 6)
            ->assertJsonPath('filters', ['category' => null, 'state' => 'low', 'search' => '']);

        $response = $this->getJson('/api/v1/reports/stock?per_page=5')
            ->assertOk()
            ->assertJsonCount(5, 'data')
            ->assertJsonPath('meta.total', 42)
            ->assertJsonPath('data.0.state', 'out')
            ->assertJsonPath('totals.products', 42)
            ->assertJsonPath('totals.units', (int) $all->sum('current_stock'))
            ->assertJsonPath('totals.stock_value', number_format($all->sum(fn ($p) => $p->unit_price * $p->current_stock), 2, '.', ''))
            ->assertJsonStructure(['data' => [['id', 'name', 'sku', 'category', 'current_stock', 'low_stock_quantity', 'state', 'state_label', 'unit_price', 'stock_value']], 'links', 'meta', 'totals', 'filters']);

        $this->assertMatchesRegularExpression('/^\d+\.\d{2}$/', $response->json('data.1.stock_value'));
        $this->assertIsString($response->json('data.1.unit_price'));
    }

    public function test_in_house_sales_match_order_lines_and_echo_the_range(): void
    {
        $lines = OrderDetail::with(['order', 'product'])->get()
            ->reject(fn ($d) => $d->order->delivery_status === DeliveryStatus::Cancelled)
            ->filter(fn ($d) => $d->product->added_by === 'admin');

        $this->getJson('/api/v1/reports/in-house-sales')
            ->assertOk()
            ->assertJsonCount($lines->pluck('product_id')->unique()->count(), 'data')
            ->assertJsonPath('totals.units', (int) $lines->sum('quantity'))
            ->assertJsonPath('totals.revenue', number_format($lines->sum(fn ($d) => $d->price * $d->quantity), 2, '.', ''))
            ->assertJsonPath('filters', ['date_from' => today()->subDays(29)->toDateString(), 'date_to' => today()->toDateString()])
            ->assertJsonStructure(['data' => [['id', 'name', 'sku', 'units', 'revenue']], 'totals' => ['products', 'units', 'revenue'], 'filters']);

        $this->getJson('/api/v1/reports/in-house-sales?date_from=2020-01-01&date_to=2020-01-31')
            ->assertOk()
            ->assertJsonPath('data', [])
            ->assertJsonPath('totals', ['products' => 0, 'units' => 0, 'revenue' => '0.00'])
            ->assertJsonPath('filters.date_to', '2020-01-31');
    }

    public function test_seller_sales_have_a_row_per_shop(): void
    {
        $this->getJson('/api/v1/reports/seller-sales')
            ->assertOk()
            ->assertJsonCount(5, 'data')
            ->assertJsonPath('totals.shops', 5)
            ->assertJsonStructure(['data' => [['id', 'shop', 'seller', 'orders', 'units', 'revenue']], 'totals' => ['shops', 'orders', 'units', 'revenue'], 'filters' => ['date_from', 'date_to']]);
    }

    public function test_wishlist_lists_every_product_of_the_category(): void
    {
        $product = Product::whereNotNull('category_id')->orderBy('id')->firstOrFail();
        foreach (User::where('email', 'like', '%@customers.wowmart.test')->get() as $c) {
            Wishlist::create(['user_id' => $c->id, 'product_id' => $product->id]);
        }

        $response = $this->getJson('/api/v1/reports/wishlist?category='.$product->category_id)
            ->assertOk()
            ->assertJsonPath('data.0', [
                'id' => $product->id, 'name' => $product->name, 'sku' => $product->sku,
                'category' => $product->category->name, 'thumbnail' => $product->thumbnail_img, 'wishlists' => 3,
            ])
            ->assertJsonPath('filters', ['category' => $product->category_id]);

        $categoryProducts = $product->category()->withCount('products')->firstOrFail()->products_count;
        $this->assertSame($categoryProducts, count($response->json('data')));
        $this->assertSame(['products' => $categoryProducts, 'wishlists' => 3], $response->json('totals'));
    }

    public function test_reports_need_an_admin(): void
    {
        Sanctum::actingAs(User::where('email', 'rafi@customers.wowmart.test')->firstOrFail());

        $this->getJson('/api/v1/reports/stock')->assertForbidden();
    }
}
