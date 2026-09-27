<?php

namespace Tests\Feature\Admin;

use App\Enums\DeliveryStatus;
use App\Enums\UserType;
use App\Models\Order;
use App\Models\User;
use App\Services\DeliveryBoyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeliveryBoyCancelRequestTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::where('email', 'admin@wowmart.test')->firstOrFail();
    }

    private function orderWithRequest(): Order
    {
        $order = Order::whereNull('cancel_requested_at')
            ->whereIn('delivery_status', [DeliveryStatus::Pending, DeliveryStatus::Confirmed])
            ->firstOrFail();
        $boy = User::where('user_type', UserType::DeliveryBoy)->firstOrFail();

        app(DeliveryBoyService::class)->assign($order, $boy);
        app(DeliveryBoyService::class)->requestCancel($order);

        return $order->fresh();
    }

    public function test_assigned_orders_and_the_cancel_flow(): void
    {
        $order = Order::whereIn('delivery_status', [DeliveryStatus::Pending, DeliveryStatus::Confirmed])
            ->whereNull('cancel_requested_at')->firstOrFail();
        $boy = User::where('user_type', UserType::DeliveryBoy)->firstOrFail();
        $code = $order->code;

        // Assign from the order page.
        $this->actingAs($this->admin)->post("/admin/all_orders/{$code}/assign-delivery-boy", [
            'delivery_boy_id' => $boy->id,
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame($boy->id, $order->fresh()->delivery_boy_id);

        // The boy asks to cancel; it lands on the list.
        app(DeliveryBoyService::class)->requestCancel($order->fresh());
        $this->actingAs($this->admin)->get('/admin/delivery-boys-cancel-requests')
            ->assertOk()->assertSee($code);

        // Declining clears the request but keeps the order.
        $this->actingAs($this->admin)->post("/admin/delivery-boys-cancel-requests/{$order->id}/decline")
            ->assertRedirect()->assertSessionHas('status');
        $this->assertNull($order->fresh()->cancel_requested_at);
        $this->assertNotSame(DeliveryStatus::Cancelled, $order->fresh()->delivery_status);
    }

    public function test_approving_a_cancel_request_cancels_the_order(): void
    {
        $order = $this->orderWithRequest();
        $code = $order->code;

        $this->actingAs($this->admin)->post("/admin/delivery-boys-cancel-requests/{$order->id}/approve")
            ->assertRedirect()->assertSessionHas('status');

        $this->assertSame(DeliveryStatus::Cancelled, $order->fresh()->delivery_status);
        $this->assertNull($order->fresh()->cancel_requested_at);
    }

    public function test_a_customer_cannot_be_assigned_as_a_delivery_boy(): void
    {
        $order = Order::whereNull('cancel_requested_at')->firstOrFail();
        $customer = User::where('user_type', UserType::Customer)->firstOrFail();

        $this->actingAs($this->admin)->post("/admin/all_orders/{$order->code}/assign-delivery-boy", [
            'delivery_boy_id' => $customer->id,
        ])->assertSessionHasErrors();
        $this->assertNull($order->fresh()->delivery_boy_id);
    }
}
