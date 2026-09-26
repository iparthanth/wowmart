<?php

namespace Tests\Feature\Admin;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderSalesListTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::where('email', 'admin@wowmart.test')->firstOrFail();
    }

    public function test_all_four_lists_open_with_the_seeded_orders(): void
    {
        $this->actingAs($this->admin)->get('/admin/inhouse-orders')
            ->assertOk()->assertSee('Inhouse orders');

        $this->actingAs($this->admin)->get('/admin/seller_orders')
            ->assertOk()->assertSee('Seller orders');

        $this->actingAs($this->admin)->get('/admin/orders_by_pickup_point')
            ->assertOk()->assertSee('Pickup point orders');

        $this->actingAs($this->admin)->get('/admin/unpaid_orders')
            ->assertOk()->assertSee('Unpaid orders');
    }

    public function test_inhouse_and_seller_lists_split_the_seeded_orders(): void
    {
        // The seeder mixes in-house and seller products across carts; every
        // seeded order must land in at least one of the two lists.
        $inhouse = $this->actingAs($this->admin)->get('/admin/inhouse-orders')->viewData('orders')->getCollection();
        $seller = $this->actingAs($this->admin)->get('/admin/seller_orders')->viewData('orders')->getCollection();
        $all = Order::count();

        $this->assertNotEmpty($inhouse);
        $this->assertNotEmpty($seller);
        $this->assertSame($all, $inhouse->merge($seller)->unique('id')->count());
    }

    public function test_an_order_shows_in_a_list_when_one_of_its_lines_matches(): void
    {
        $order = Order::has('details')->firstOrFail();

        // A pickup line moves the order into the pickup list without touching
        // the others it belongs to.
        $order->details->first()->forceFill(['shipping_type' => 'pickup_point'])->save();

        $pickup = $this->actingAs($this->admin)->get('/admin/orders_by_pickup_point')->viewData('orders');
        $this->assertSame([$order->id], $pickup->getCollection()->pluck('id')->all());
    }

    public function test_the_unpaid_list_holds_only_unpaid_orders(): void
    {
        $unpaid = $this->actingAs($this->admin)->get('/admin/unpaid_orders')->viewData('orders')->getCollection();

        $this->assertNotEmpty($unpaid);
        $this->assertSame(
            Order::where('payment_status', 'unpaid')->count(),
            $unpaid->count(),
        );
        $this->assertSame(
            Order::where('payment_status', 'unpaid')->pluck('id')->sort()->values()->all(),
            $unpaid->pluck('id')->sort()->values()->all(),
        );
    }

    public function test_a_fixed_list_keeps_its_constraint_while_searching(): void
    {
        // A search that matches nothing must not fall back to other lists.
        $this->actingAs($this->admin)->get('/admin/unpaid_orders?search=zzz-no-such-order')
            ->assertOk()
            ->assertViewHas('orders', fn ($page) => $page->isEmpty());

        // A shared filter still works on top of the fixed constraint.
        $unpaidTotal = Order::where('payment_status', 'unpaid')->count();
        $this->actingAs($this->admin)->get('/admin/unpaid_orders?delivery_status=pending')
            ->assertOk()
            ->assertViewHas('orders', fn ($page) => $page->total() <= $unpaidTotal);
    }

    public function test_the_all_orders_page_is_unchanged(): void
    {
        $this->actingAs($this->admin)->get('/admin/all_orders')
            ->assertOk()
            ->assertViewHas('orders', fn ($page) => $page->total() === Order::count());
    }
}
