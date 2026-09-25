<?php

namespace Tests\Feature\Admin;

use App\Enums\DeliveryStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\User;
use App\Services\OrderService;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::where('email', 'admin@wowmart.test')->firstOrFail();
    }

    private function listed(array $query = []): int
    {
        return $this->actingAs($this->admin)
            ->get(route('admin.orders.index', $query))
            ->assertOk()
            ->viewData('orders')
            ->total();
    }

    private function orderIn(DeliveryStatus $status): Order
    {
        return Order::where('delivery_status', $status)->orderBy('id')->firstOrFail();
    }

    private function service(): OrderService
    {
        return app(OrderService::class);
    }

    /** Stock figures for every product on the order, keyed by product id. */
    private function stockOf(Order $order): array
    {
        return $order->details()->get()->mapWithKeys(fn ($line) => [$line->product_id => [
            'current_stock' => Product::find($line->product_id)->current_stock,
            'num_of_sale' => Product::find($line->product_id)->num_of_sale,
            'row' => ProductStock::where('product_id', $line->product_id)->where('variant', '')->value('qty'),
            'qty' => $line->quantity,
        ]])->all();
    }

    public function test_filters_match_the_seeded_counts(): void
    {
        $this->assertSame(13, $this->listed());
        $this->assertSame(2, $this->listed(['delivery_status' => 'pending'])); // the dashboard's link
        $this->assertSame(6, $this->listed(['delivery_status' => 'delivered']));
        $this->assertSame(1, $this->listed(['delivery_status' => 'cancelled']));
        $this->assertSame(5, $this->listed(['payment_status' => 'unpaid']));
        $this->assertSame(8, $this->listed(['payment_status' => 'paid']));
        $this->assertSame(0, $this->listed(['delivery_status' => 'pending', 'payment_status' => 'paid']));
    }

    public function test_unknown_filter_values_are_ignored(): void
    {
        $this->assertSame(13, $this->listed(['delivery_status' => 'processing', 'payment_status' => 'maybe', 'sort' => 'drop table', 'date_from' => '2026-02-31', 'date_to' => 'yesterday']));
    }

    public function test_search_by_code_customer_and_phone(): void
    {
        $order = $this->orderIn(DeliveryStatus::Confirmed);

        $this->assertSame(1, $this->listed(['search' => $order->code]));
        $this->assertSame(5, $this->listed(['search' => 'nusrat@customers']));
        $this->assertSame(4, $this->listed(['search' => 'Tanvir']));
        $this->assertSame(1, $this->listed(['search' => $order->shipping_address['phone']]));
        $this->assertSame(0, $this->listed(['search' => 'no-such-order']));
    }

    public function test_typed_like_wildcards_match_only_themselves(): void
    {
        $this->assertSame(0, $this->listed(['search' => '_']));
        $this->assertSame(0, $this->listed(['search' => '%']));
        $this->assertSame(0, $this->listed(['search' => 'nusrat_customers']));
    }

    public function test_date_range_is_inclusive_whole_days(): void
    {
        $yesterday = today()->subDay()->toDateString();

        $this->assertSame(1, $this->listed(['date_from' => $yesterday, 'date_to' => $yesterday]));
        $this->assertSame(2, $this->listed(['date_from' => $yesterday, 'date_to' => today()->toDateString()]));
        $this->assertSame(2, $this->listed(['date_from' => $yesterday]));
        $this->assertSame(11, $this->listed(['date_to' => today()->subDays(2)->toDateString()]));
    }

    public function test_sort_by_amount_puts_the_largest_order_first(): void
    {
        $first = $this->actingAs($this->admin)->get(route('admin.orders.index', ['sort' => 'amount']))
            ->assertOk()->viewData('orders')->first();

        $this->assertSame((float) Order::max('grand_total'), (float) $first->grand_total);
    }

    public function test_the_list_shows_codes_linked_to_the_detail_page(): void
    {
        $order = $this->orderIn(DeliveryStatus::Pending);

        $this->actingAs($this->admin)->get(route('admin.orders.index'))
            ->assertOk()
            ->assertSee(route('admin.orders.show', $order))
            ->assertSee('Cash on delivery')
            ->assertSee('In-house');
    }

    public function test_the_detail_url_uses_the_order_code(): void
    {
        $order = $this->orderIn(DeliveryStatus::Pending);
        $url = route('admin.orders.show', $order);

        $this->assertStringEndsWith('/admin/all_orders/'.$order->code, $url);

        $line = $order->details()->with('product')->first();

        $this->actingAs($this->admin)->get($url)
            ->assertOk()
            ->assertSee($order->code)
            ->assertSee($line->product->name)
            ->assertSee($order->shipping_address['city'])
            ->assertSee('Confirm order')
            ->assertSee('Cancel order')
            ->assertSee('Mark as paid');

        $this->actingAs($this->admin)->get('/admin/all_orders/'.$order->id)->assertNotFound();
    }

    public function test_final_orders_show_no_status_buttons(): void
    {
        $this->actingAs($this->admin)->get(route('admin.orders.show', $this->orderIn(DeliveryStatus::Delivered)))
            ->assertOk()
            ->assertDontSee('Cancel order')
            ->assertDontSee('Mark as delivered')
            ->assertSee('Mark as unpaid');

        $this->actingAs($this->admin)->get(route('admin.orders.show', $this->orderIn(DeliveryStatus::Cancelled)))
            ->assertOk()
            ->assertDontSee('Confirm order')
            ->assertDontSee('Mark as paid');
    }

    public function test_a_deleted_product_still_shows_on_the_order(): void
    {
        $order = $this->orderIn(DeliveryStatus::Delivered);
        $order->details()->first()->forceFill(['product_id' => null])->save();

        $this->actingAs($this->admin)->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSee('Deleted product');
    }

    public function test_orders_cannot_be_created_or_deleted_from_the_admin(): void
    {
        $order = $this->orderIn(DeliveryStatus::Pending);

        $this->assertFalse(Route::has('admin.orders.create'));
        $this->assertFalse(Route::has('admin.orders.destroy'));
        $this->actingAs($this->admin)->delete('/admin/all_orders/'.$order->code)->assertStatus(405);
        $this->assertModelExists($order);
    }

    public function test_every_transition_follows_the_state_machine(): void
    {
        $order = $this->orderIn(DeliveryStatus::Pending);

        foreach (DeliveryStatus::cases() as $from) {
            foreach (DeliveryStatus::cases() as $to) {
                // Written through a query: the in-memory model is stale after the service moved it.
                Order::whereKey($order->id)->update(['delivery_status' => $from, 'payment_status' => PaymentStatus::Unpaid]);

                try {
                    $this->service()->changeDeliveryStatus($order, $to);
                    $moved = true;
                } catch (DomainException) {
                    $moved = false;
                }

                $this->assertSame($from->canBecome($to), $moved, "{$from->value} -> {$to->value}");
                $this->assertSame($moved ? $to : $from, $order->fresh()->delivery_status, "{$from->value} -> {$to->value}");
            }
        }
    }

    public function test_refusals_say_what_to_do(): void
    {
        $this->expectExceptionMessage('This order is delivered, so it cannot be cancelled. Delivered and cancelled orders are final.');
        $this->service()->changeDeliveryStatus($this->orderIn(DeliveryStatus::Delivered), DeliveryStatus::Cancelled);
    }

    public function test_the_status_change_reaches_every_line(): void
    {
        $order = $this->orderIn(DeliveryStatus::Pending);

        $this->actingAs($this->admin)
            ->post(route('admin.orders.delivery-status', $order), ['status' => 'confirmed'])
            ->assertRedirect(route('admin.orders.show', $order))
            ->assertSessionHas('status', "Order {$order->code} is now confirmed.");

        $this->assertSame(DeliveryStatus::Confirmed, $order->fresh()->delivery_status);
        $this->assertSame(['confirmed'], $order->details()->pluck('delivery_status')->map->value->unique()->values()->all());
    }

    public function test_a_refused_transition_flashes_the_reason(): void
    {
        $order = $this->orderIn(DeliveryStatus::Pending);

        $this->actingAs($this->admin)
            ->from(route('admin.orders.show', $order))
            ->post(route('admin.orders.delivery-status', $order), ['status' => 'delivered'])
            ->assertRedirect(route('admin.orders.show', $order))
            ->assertSessionHas('error', fn (string $m) => str_contains($m, 'Next it can be: Confirmed or Cancelled'));

        $this->actingAs($this->admin)
            ->post(route('admin.orders.delivery-status', $order), ['status' => 'processing'])
            ->assertSessionHasErrors('status');

        $this->assertSame(DeliveryStatus::Pending, $order->fresh()->delivery_status);
    }

    public function test_cancelling_returns_stock_to_product_stock_row_and_sale_count(): void
    {
        $order = $this->orderIn(DeliveryStatus::Pending);
        $before = $this->stockOf($order);
        $this->assertNotEmpty($before);

        $this->actingAs($this->admin)
            ->post(route('admin.orders.delivery-status', $order), ['status' => 'cancelled'])
            ->assertSessionHas('status', fn (string $m) => str_contains($m, 'back in stock'));

        foreach ($this->stockOf($order) as $id => $after) {
            $b = $before[$id];
            $this->assertSame($b['current_stock'] + $b['qty'], $after['current_stock']);
            $this->assertSame($b['row'] + $b['qty'], $after['row']);
            $this->assertSame(max(0, $b['num_of_sale'] - $b['qty']), $after['num_of_sale']);
        }

        $this->assertSame(['cancelled'], $order->details()->pluck('delivery_status')->map->value->unique()->values()->all());
    }

    public function test_sale_count_never_goes_below_zero(): void
    {
        $order = $this->orderIn(DeliveryStatus::Pending);
        $line = $order->details()->first();
        Product::whereKey($line->product_id)->update(['num_of_sale' => 0]);

        $this->service()->changeDeliveryStatus($order, DeliveryStatus::Cancelled);

        $this->assertSame(0, Product::find($line->product_id)->num_of_sale);
    }

    public function test_cancelling_twice_restocks_once(): void
    {
        $order = $this->orderIn(DeliveryStatus::Confirmed);
        $before = $this->stockOf($order);

        // A double-click: the second request arrives holding the same stale page.
        $this->actingAs($this->admin)->post(route('admin.orders.delivery-status', $order), ['status' => 'cancelled'])
            ->assertSessionHas('status');
        $this->actingAs($this->admin)->post(route('admin.orders.delivery-status', $order), ['status' => 'cancelled'])
            ->assertSessionHas('error', 'This order is already cancelled.');

        // And a stale model in memory is refused too: the service re-reads under lock.
        try {
            $this->service()->changeDeliveryStatus($order, DeliveryStatus::Cancelled);
            $this->fail('The second cancel should have been refused.');
        } catch (DomainException) {
        }

        foreach ($this->stockOf($order) as $id => $after) {
            $this->assertSame($before[$id]['current_stock'] + $before[$id]['qty'], $after['current_stock']);
            $this->assertSame($before[$id]['row'] + $before[$id]['qty'], $after['row']);
        }
    }

    public function test_cancelling_restocks_the_matching_variant_and_skips_deleted_products(): void
    {
        $order = $this->orderIn(DeliveryStatus::Confirmed);
        [$variantLine, $goneLine] = $order->details()->orderBy('id')->get()->all();

        $red = ProductStock::create(['product_id' => $variantLine->product_id, 'variant' => 'Red', 'price' => 10, 'qty' => 5]);
        $plain = ProductStock::where('product_id', $variantLine->product_id)->where('variant', '')->value('qty');
        $variantLine->forceFill(['variation' => 'Red'])->save();
        $goneLine->forceFill(['product_id' => null])->save();

        $this->service()->changeDeliveryStatus($order, DeliveryStatus::Cancelled);

        $this->assertSame(5 + $variantLine->quantity, $red->fresh()->qty);
        $this->assertSame($plain, ProductStock::where('product_id', $variantLine->product_id)->where('variant', '')->value('qty'));
    }

    public function test_a_delivered_cash_on_delivery_order_becomes_paid(): void
    {
        $order = Order::where('delivery_status', DeliveryStatus::PickedUp)->firstOrFail();
        $this->assertSame(PaymentStatus::Unpaid, $order->payment_status);

        $this->service()->changeDeliveryStatus($order, DeliveryStatus::OnTheWay);
        $this->assertSame(PaymentStatus::Unpaid, $order->fresh()->payment_status); // only delivery collects the cash

        $this->actingAs($this->admin)->post(route('admin.orders.delivery-status', $order), ['status' => 'delivered'])
            ->assertSessionHas('status', fn (string $m) => str_contains($m, 'marked as paid'));

        $this->assertSame(PaymentStatus::Paid, $order->fresh()->payment_status);
        $this->assertSame(['paid'], $order->details()->pluck('payment_status')->map->value->unique()->values()->all());
    }

    public function test_a_prepaid_order_is_not_touched_by_the_cod_rule(): void
    {
        $order = $this->orderIn(DeliveryStatus::OnTheWay);
        $order->forceFill(['payment_type' => 'bkash', 'payment_status' => PaymentStatus::Unpaid])->save();

        $this->service()->changeDeliveryStatus($order, DeliveryStatus::Delivered);

        $this->assertSame(PaymentStatus::Unpaid, $order->fresh()->payment_status);
    }

    public function test_payment_status_updates_order_and_lines(): void
    {
        $order = $this->orderIn(DeliveryStatus::Pending);

        $this->actingAs($this->admin)->post(route('admin.orders.payment-status', $order), ['status' => 'paid'])
            ->assertRedirect(route('admin.orders.show', $order))
            ->assertSessionHas('status', "Order {$order->code} is marked as paid.");

        $this->assertSame(PaymentStatus::Paid, $order->fresh()->payment_status);
        $this->assertSame(['paid'], $order->details()->pluck('payment_status')->map->value->unique()->values()->all());

        $this->actingAs($this->admin)->post(route('admin.orders.payment-status', $order), ['status' => 'unpaid']);
        $this->assertSame(PaymentStatus::Unpaid, $order->fresh()->payment_status);
    }

    public function test_a_cancelled_order_cannot_be_marked_paid(): void
    {
        $order = $this->orderIn(DeliveryStatus::Cancelled);

        $this->actingAs($this->admin)
            ->from(route('admin.orders.show', $order))
            ->post(route('admin.orders.payment-status', $order), ['status' => 'paid'])
            ->assertSessionHas('error', fn (string $m) => str_contains($m, 'cannot be marked as paid'));

        $this->assertSame(PaymentStatus::Unpaid, $order->fresh()->payment_status);

        // Recording a refund on a cancelled order that was paid is still allowed.
        $order->forceFill(['payment_status' => PaymentStatus::Paid])->save();
        $this->service()->setPaymentStatus($order, PaymentStatus::Unpaid);
        $this->assertSame(PaymentStatus::Unpaid, $order->fresh()->payment_status);
    }
}
