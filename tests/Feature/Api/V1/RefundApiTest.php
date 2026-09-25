<?php

namespace Tests\Feature\Api\V1;

use App\Enums\RefundStatus;
use App\Models\BusinessSetting;
use App\Models\Product;
use App\Models\RefundRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RefundApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Sanctum::actingAs(User::where('email', 'admin@wowmart.test')->firstOrFail());
    }

    private function refundIn(RefundStatus $status): RefundRequest
    {
        return RefundRequest::where('refund_status', $status)->firstOrFail();
    }

    public function test_the_list_is_paginated_and_filtered(): void
    {
        $this->getJson('/api/v1/refund-requests?per_page=2')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.total', 3)
            ->assertJsonStructure(['data' => [[
                'id', 'status', 'status_label', 'refund_amount', 'reason', 'requested_at', 'order' => ['id', 'code'],
                'line' => ['id', 'product', 'quantity', 'price'], 'customer', 'seller',
            ]], 'links', 'meta']);

        foreach (RefundStatus::cases() as $s) {
            $this->getJson('/api/v1/refund-requests?status='.$s->value)->assertOk()->assertJsonPath('meta.total', 1)->assertJsonPath('data.0.status', $s->value);
        }
        $this->getJson('/api/v1/refund-requests?status=nonsense')->assertOk()->assertJsonPath('meta.total', 3);

        $pending = $this->refundIn(RefundStatus::Pending);
        $this->getJson('/api/v1/refund-requests?search='.$pending->order->code)
            ->assertOk()->assertJsonPath('meta.total', 1)->assertJsonPath('data.0.id', $pending->id);
        $this->getJson('/api/v1/refund-requests?search='.urlencode($pending->customer->name))
            ->assertOk()->assertJsonPath('data.0.customer.name', $pending->customer->name);
    }

    public function test_show_returns_money_as_a_string_and_survives_a_deleted_product(): void
    {
        $refund = $this->refundIn(RefundStatus::Pending);
        $line = $refund->orderDetail;

        $this->getJson("/api/v1/refund-requests/{$refund->id}")
            ->assertOk()
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.status_label', 'Pending')
            ->assertJsonPath('data.refund_amount', number_format((float) $line->price * $line->quantity, 2, '.', ''))
            ->assertJsonPath('data.order.code', $refund->order->code)
            ->assertJsonPath('data.line.product.id', $line->product_id);

        Product::whereKey($line->product_id)->delete();

        $this->getJson("/api/v1/refund-requests/{$refund->id}")->assertOk()->assertJsonPath('data.line.product', null);
        $this->getJson('/api/v1/refund-requests/999999')->assertNotFound();
    }

    public function test_status_approves_or_rejects_once(): void
    {
        $refund = $this->refundIn(RefundStatus::Pending);

        $this->patchJson("/api/v1/refund-requests/{$refund->id}/status", ['status' => 'approved'])
            ->assertOk()
            ->assertJsonPath('data.status', 'approved');

        $this->patchJson("/api/v1/refund-requests/{$refund->id}/status", ['status' => 'rejected'])
            ->assertStatus(409)
            ->assertJsonPath('message', 'This refund was already approved. A decision is final, so there is nothing left to do.');

        $this->assertSame(RefundStatus::Approved, $refund->fresh()->refund_status);

        $this->patchJson('/api/v1/refund-requests/'.$this->refundIn(RefundStatus::Rejected)->id.'/status', ['status' => 'approved'])
            ->assertStatus(409);
    }

    public function test_status_only_accepts_a_decision(): void
    {
        $refund = $this->refundIn(RefundStatus::Pending);

        foreach (['pending', 'refunded', ['approved'], null] as $bad) {
            $this->patchJson("/api/v1/refund-requests/{$refund->id}/status", ['status' => $bad])
                ->assertUnprocessable()
                ->assertJsonValidationErrors('status');
        }

        $this->assertSame(RefundStatus::Pending, $refund->fresh()->refund_status);
    }

    public function test_settings_default_validate_and_persist(): void
    {
        $this->getJson('/api/v1/refund-settings')->assertOk()->assertExactJson(['data' => ['refund_request_time' => 7]]);

        foreach ([0, 366, '2.5', 'x', [3]] as $bad) {
            $this->putJson('/api/v1/refund-settings', ['refund_request_time' => $bad])
                ->assertUnprocessable()
                ->assertJsonValidationErrors('refund_request_time');
        }
        $this->assertNull(BusinessSetting::get('refund_request_time'));

        $this->putJson('/api/v1/refund-settings', ['refund_request_time' => 30])
            ->assertOk()
            ->assertExactJson(['data' => ['refund_request_time' => 30]]);

        $this->getJson('/api/v1/refund-settings')->assertOk()->assertJsonPath('data.refund_request_time', 30);

        $this->patchJson('/api/v1/refund-settings', ['refund_request_time' => 5])->assertStatus(405);
    }
}
