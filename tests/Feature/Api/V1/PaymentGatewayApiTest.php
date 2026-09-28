<?php

namespace Tests\Feature\Api\V1;

use App\Models\BusinessSetting;
use App\Models\OfflinePackagePayment;
use App\Models\Order;
use App\Models\User;
use App\Models\WalletRecharge;
use App\Services\PaymentGatewayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PaymentGatewayApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Sanctum::actingAs(User::where('email', 'admin@wowmart.test')->firstOrFail());
    }

    public function test_gateway_settings_round_trip(): void
    {
        $this->getJson('/api/v1/payment-methods')->assertOk();

        $this->getJson('/api/v1/payment-methods/stripe')
            ->assertOk()->assertJsonPath('data.key', '');

        $this->putJson('/api/v1/payment-methods/stripe', [
            'key' => 'pk_test_123',
            'secret' => 'sk_test_123',
            'types' => ['checkout'],
        ])->assertOk()->assertJsonPath('data.key', 'pk_test_123');

        $this->assertSame('pk_test_123', BusinessSetting::get(PaymentGatewayService::settingKey('stripe', 'key')));

        $this->getJson('/api/v1/payment-methods/not-a-gateway')->assertNotFound();
        $this->putJson('/api/v1/payment-methods/not-a-gateway', [])->assertNotFound();
    }

    public function test_manual_methods_resource_round_trip(): void
    {
        $this->getJson('/api/v1/manual-payment-methods')->assertOk();

        $id = $this->postJson('/api/v1/manual-payment-methods', [
            'name' => 'Upay',
            'instructions' => 'Send to 01611-000444.',
        ])->assertCreated()
            ->assertJsonPath('data.name', 'Upay')
            ->json('data.id');

        $this->getJson("/api/v1/manual-payment-methods/{$id}")->assertOk()->assertJsonPath('data.id', $id);

        $this->putJson("/api/v1/manual-payment-methods/{$id}", [
            'name' => 'Upay mobile',
            'status' => '0',
        ])->assertOk()->assertJsonPath('data.status', false);

        $this->deleteJson("/api/v1/manual-payment-methods/{$id}")->assertNoContent();
        $this->assertDatabaseMissing('manual_payment_methods', ['id' => $id]);
    }

    public function test_offline_orders_and_mark_paid(): void
    {
        $this->getJson('/api/v1/offline/orders')->assertOk();

        $order = Order::where('payment_type', 'manual_payment')->where('payment_status', 'unpaid')->firstOrFail();

        $this->getJson("/api/v1/offline/orders/{$order->id}")->assertOk()->assertJsonPath('data.id', $order->id);

        $this->postJson("/api/v1/offline/orders/{$order->id}/mark-paid")
            ->assertOk()->assertJsonPath('data.payment_status', 'paid');

        $this->postJson("/api/v1/offline/orders/{$order->id}/mark-paid")->assertConflict();
    }

    public function test_wallet_recharge_decision_via_api(): void
    {
        $this->getJson('/api/v1/offline/wallet')->assertOk();

        $recharge = WalletRecharge::where('approval', 'pending')->firstOrFail();
        $customer = $recharge->customer;
        $before = (float) $customer->wallet_balance;

        $this->postJson("/api/v1/offline/wallet/{$recharge->id}/decision", ['approval' => 'approved'])
            ->assertOk()->assertJsonPath('data.approval', 'approved');

        $this->assertEquals($before + (float) $recharge->amount, (float) $customer->fresh()->wallet_balance);

        $this->postJson("/api/v1/offline/wallet/{$recharge->id}/decision", ['approval' => 'approved'])->assertConflict();
    }

    public function test_package_payments_filtered_by_type(): void
    {
        $this->getJson('/api/v1/offline/package-payments?type=customer')
            ->assertOk();

        $this->getJson('/api/v1/offline/package-payments?type=seller')
            ->assertOk();

        $this->getJson('/api/v1/offline/package-payments?type=alien')->assertNotFound();

        $payment = OfflinePackagePayment::where('approval', 'pending')->where('package_type', 'seller')->firstOrFail();

        $this->postJson("/api/v1/offline/package-payments/{$payment->id}/decision", ['approval' => 'rejected'])
            ->assertOk()->assertJsonPath('data.approval', 'rejected');
    }

    public function test_the_gateway_api_needs_an_admin(): void
    {
        Sanctum::actingAs(User::where('email', 'rafi@customers.wowmart.test')->firstOrFail());

        $this->getJson('/api/v1/payment-methods')->assertForbidden();
        $this->getJson('/api/v1/offline/wallet')->assertForbidden();
        $this->postJson('/api/v1/offline/orders/1/mark-paid')->assertForbidden();
    }
}
