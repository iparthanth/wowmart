<?php

namespace Tests\Feature\Api\V1;

use App\Enums\DeliveryStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrderApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Sanctum::actingAs(User::where('email', 'admin@wowmart.test')->firstOrFail());
    }

    private function orderIn(DeliveryStatus $status): Order
    {
        return Order::where('delivery_status', $status)->orderBy('id')->firstOrFail();
    }

    public function test_the_list_is_paginated_and_filtered_like_the_panel(): void
    {
        $this->getJson('/api/v1/orders?per_page=5')
            ->assertOk()
            ->assertJsonCount(5, 'data')
            ->assertJsonPath('meta.total', 13)
            ->assertJsonStructure(['data' => [[
                'id', 'code', 'placed_at', 'delivery_status', 'delivery_status_label', 'payment_status',
                'payment_status_label', 'payment_type', 'payment_type_label', 'grand_total', 'item_count', 'customer', 'seller',
            ]], 'links', 'meta']);

        $this->getJson('/api/v1/orders?delivery_status=pending')->assertOk()->assertJsonPath('meta.total', 2);
        $this->getJson('/api/v1/orders?payment_status=unpaid')->assertOk()->assertJsonPath('meta.total', 6); // the manual-payment seed flips one paid order back
        $this->getJson('/api/v1/orders?delivery_status=nonsense')->assertOk()->assertJsonPath('meta.total', 13);

        $code = $this->orderIn(DeliveryStatus::Confirmed)->code;
        $this->getJson('/api/v1/orders?search='.$code)->assertOk()->assertJsonPath('meta.total', 1)->assertJsonPath('data.0.code', $code);
    }

    public function test_show_by_code_includes_lines_customer_and_money_strings(): void
    {
        $order = $this->orderIn(DeliveryStatus::Pending);
        $line = $order->details()->orderBy('id')->firstOrFail();

        $this->getJson("/api/v1/orders/{$order->code}")
            ->assertOk()
            ->assertJsonPath('data.code', $order->code)
            ->assertJsonPath('data.delivery_status', 'pending')
            ->assertJsonPath('data.delivery_status_label', 'Pending')
            ->assertJsonPath('data.payment_type_label', 'Cash on delivery')
            ->assertJsonPath('data.grand_total', number_format((float) $order->grand_total, 2, '.', ''))
            ->assertJsonPath('data.customer.email', $order->customer->email)
            ->assertJsonPath('data.seller', null)
            ->assertJsonPath('data.shipping_address.city', $order->shipping_address['city'])
            ->assertJsonPath('data.item_count', $order->details()->count())
            ->assertJsonPath('data.lines.0.product.id', $line->product_id)
            ->assertJsonPath('data.lines.0.quantity', $line->quantity)
            ->assertJsonPath('data.lines.0.price', number_format((float) $line->price, 2, '.', ''))
            ->assertJsonPath('data.lines.0.line_total', number_format($line->lineTotal(), 2, '.', ''))
            ->assertJsonPath('data.lines.0.delivery_status_label', 'Pending')
            ->assertJsonPath('data.lines.0.payment_status_label', $line->payment_status->label())
            ->assertJsonPath('data.placed_at', $order->created_at->toIso8601String());

        $this->getJson("/api/v1/orders/{$order->id}")->assertNotFound();
    }

    public function test_delivery_status_transitions_and_refusals(): void
    {
        $order = $this->orderIn(DeliveryStatus::Pending);

        $this->patchJson("/api/v1/orders/{$order->code}/delivery-status", ['status' => 'confirmed'])
            ->assertOk()
            ->assertJsonPath('data.delivery_status', 'confirmed')
            ->assertJsonPath('data.lines.0.delivery_status', 'confirmed');

        $this->patchJson("/api/v1/orders/{$order->code}/delivery-status", ['status' => 'delivered'])
            ->assertStatus(409)
            ->assertJsonPath('message', fn (string $m) => str_contains($m, 'cannot be marked as delivered'));

        $this->patchJson("/api/v1/orders/{$order->code}/delivery-status", ['status' => 'processing'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('status');
    }

    public function test_cod_delivery_marks_the_order_paid(): void
    {
        $order = $this->orderIn(DeliveryStatus::OnTheWay);
        $order->forceFill(['payment_status' => PaymentStatus::Unpaid])->save();

        $this->patchJson("/api/v1/orders/{$order->code}/delivery-status", ['status' => 'delivered'])
            ->assertOk()
            ->assertJsonPath('data.payment_status', 'paid')
            ->assertJsonPath('data.lines.0.payment_status', 'paid');
    }

    public function test_cancel_restocks_once_and_a_second_cancel_is_409(): void
    {
        $order = $this->orderIn(DeliveryStatus::Pending);
        $line = $order->details()->orderBy('id')->firstOrFail();
        $stock = Product::find($line->product_id)->current_stock;

        $this->patchJson("/api/v1/orders/{$order->code}/delivery-status", ['status' => 'cancelled'])
            ->assertOk()
            ->assertJsonPath('data.delivery_status', 'cancelled');

        $this->patchJson("/api/v1/orders/{$order->code}/delivery-status", ['status' => 'cancelled'])
            ->assertStatus(409)
            ->assertJsonPath('message', 'This order is already cancelled.');

        $this->assertSame($stock + $line->quantity, Product::find($line->product_id)->current_stock);
    }

    public function test_payment_status_and_the_cancelled_rule(): void
    {
        $order = $this->orderIn(DeliveryStatus::Pending);

        $this->patchJson("/api/v1/orders/{$order->code}/payment-status", ['status' => 'paid'])
            ->assertOk()
            ->assertJsonPath('data.payment_status', 'paid')
            ->assertJsonPath('data.payment_status_label', 'Paid');

        $this->patchJson("/api/v1/orders/{$order->code}/payment-status", [])->assertUnprocessable()->assertJsonValidationErrors('status');

        $cancelled = $this->orderIn(DeliveryStatus::Cancelled);
        $this->patchJson("/api/v1/orders/{$cancelled->code}/payment-status", ['status' => 'paid'])
            ->assertStatus(409)
            ->assertJsonPath('message', fn (string $m) => str_contains($m, 'cannot be marked as paid'));
        $this->assertSame(PaymentStatus::Unpaid, $cancelled->fresh()->payment_status);
    }

    public function test_orders_cannot_be_created_or_deleted(): void
    {
        $order = $this->orderIn(DeliveryStatus::Pending);

        $this->postJson('/api/v1/orders', [])->assertStatus(405);
        $this->deleteJson("/api/v1/orders/{$order->code}")->assertStatus(405);
    }
}
