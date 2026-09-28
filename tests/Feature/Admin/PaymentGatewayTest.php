<?php

namespace Tests\Feature\Admin;

use App\Models\BusinessSetting;
use App\Models\ManualPaymentMethod;
use App\Models\OfflinePackagePayment;
use App\Models\Order;
use App\Models\User;
use App\Models\WalletRecharge;
use App\Services\PaymentGatewayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentGatewayTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::where('email', 'admin@wowmart.test')->firstOrFail();
    }

    public function test_the_six_gateway_screens_render(): void
    {
        $this->actingAs($this->admin)->get('/admin/payment-methods')
            ->assertOk()->assertSee('Paypal Client Id')->assertSee('BKASH APP KEY');

        $this->actingAs($this->admin)->get('/admin/manual-payment-methods')
            ->assertOk()->assertSee('bKash manual payment');

        $this->actingAs($this->admin)->get('/admin/offline/orders')
            ->assertOk()->assertSee('Offline payment orders');

        $this->actingAs($this->admin)->get('/admin/offline/wallet')
            ->assertOk()->assertSee('BKS8H2K91LQ');

        $this->actingAs($this->admin)->get('/admin/offline/customer-package-payments')
            ->assertOk()->assertSee('Gold member — yearly');

        $this->actingAs($this->admin)->get('/admin/offline/seller-package-payments')
            ->assertOk()->assertSee('Premium seller — yearly');
    }

    public function test_gateway_credentials_are_saved_per_card(): void
    {
        $this->actingAs($this->admin)->post('/admin/payment-methods/paypal', [
            'client_id' => 'PP-CLIENT',
            'client_secret' => 'PP-SECRET',
            'sandbox' => '1',
            'types' => ['checkout', 'wallet'],
        ])->assertRedirect()->assertSessionHas('status');

        $this->assertSame('PP-CLIENT', BusinessSetting::get(PaymentGatewayService::settingKey('paypal', 'client_id')));
        $this->assertSame('1', BusinessSetting::get(PaymentGatewayService::settingKey('paypal', 'sandbox')));
        $types = json_decode(BusinessSetting::get(PaymentGatewayService::settingKey('paypal', 'types')), true);
        $this->assertSame(['checkout', 'wallet'], $types);

        // An unknown gateway card is a 404.
        $this->actingAs($this->admin)->post('/admin/payment-methods/not-a-gateway', [])->assertNotFound();
    }

    public function test_manual_methods_crud(): void
    {
        $this->actingAs($this->admin)->post('/admin/manual-payment-methods', [
            'name' => 'Rocket',
            'instructions' => 'Dial *322# and send to 01911-000333.',
            'status' => '1',
        ])->assertRedirect()->assertSessionHas('status');

        $rocket = ManualPaymentMethod::where('name', 'Rocket')->firstOrFail();

        $this->actingAs($this->admin)->get("/admin/manual-payment-methods/{$rocket->id}/edit")
            ->assertOk()->assertSee('Rocket');

        $this->actingAs($this->admin)->put("/admin/manual-payment-methods/{$rocket->id}", [
            'name' => 'Rocket (DBBL)',
            'instructions' => $rocket->instructions,
            'status' => '0',
        ])->assertRedirect()->assertSessionHas('status');
        $this->assertSame('Rocket (DBBL)', $rocket->fresh()->name);
        $this->assertFalse($rocket->fresh()->status);

        $this->actingAs($this->admin)->delete("/admin/manual-payment-methods/{$rocket->id}")
            ->assertRedirect()->assertSessionHas('status');
        $this->assertNull($rocket->fresh());
    }

    public function test_an_offline_order_is_marked_paid_once(): void
    {
        $unpaid = Order::where('payment_type', 'manual_payment')->where('payment_status', 'unpaid')->firstOrFail();

        $this->actingAs($this->admin)->post("/admin/offline/orders/{$unpaid->id}/mark-paid")
            ->assertRedirect()->assertSessionHas('status');
        $this->assertSame('paid', $unpaid->fresh()->payment_status->value);

        $this->actingAs($this->admin)->post("/admin/offline/orders/{$unpaid->id}/mark-paid")
            ->assertRedirect()->assertSessionHas('error');
    }

    public function test_approving_a_wallet_recharge_credits_the_wallet_once(): void
    {
        $recharge = WalletRecharge::where('approval', 'pending')->firstOrFail();
        $customer = $recharge->customer;
        $before = (float) $customer->wallet_balance;

        $this->actingAs($this->admin)->post("/admin/offline/wallet/{$recharge->id}/decision", ['approval' => 'approved'])
            ->assertRedirect()->assertSessionHas('status');

        $this->assertSame('approved', $recharge->fresh()->approval);
        $this->assertEquals($before + (float) $recharge->amount, (float) $customer->fresh()->wallet_balance);

        $this->actingAs($this->admin)->post("/admin/offline/wallet/{$recharge->id}/decision", ['approval' => 'rejected'])
            ->assertRedirect()->assertSessionHas('error');
        $this->assertEquals($before + (float) $recharge->amount, (float) $customer->fresh()->wallet_balance);
    }

    public function test_package_payment_decisions(): void
    {
        $payment = OfflinePackagePayment::where('approval', 'pending')->firstOrFail();

        $this->actingAs($this->admin)->post("/admin/offline/package-payments/{$payment->id}/decision", ['approval' => 'approved'])
            ->assertRedirect()->assertSessionHas('status');
        $this->assertSame('approved', $payment->fresh()->approval);

        $this->actingAs($this->admin)->post("/admin/offline/package-payments/{$payment->id}/decision", ['approval' => 'approved'])
            ->assertRedirect()->assertSessionHas('error');
    }
}
