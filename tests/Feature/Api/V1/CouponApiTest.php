<?php

namespace Tests\Feature\Api\V1;

use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CouponApiTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::where('email', 'admin@wowmart.test')->firstOrFail();
        Sanctum::actingAs($this->admin);
    }

    public function test_the_list_is_paginated_with_details_and_state(): void
    {
        $this->getJson('/api/v1/coupons?per_page=2')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.total', 3)
            ->assertJsonStructure(['data' => [['id', 'type', 'code', 'discount', 'discount_type', 'details', 'start_date', 'end_date', 'status', 'state', 'times_used']], 'links', 'meta']);

        $wow = Coupon::where('code', 'WOW100')->firstOrFail();
        $this->getJson("/api/v1/coupons/{$wow->id}")
            ->assertOk()
            ->assertJsonPath('data.type', 'cart_base')
            ->assertJsonPath('data.discount', '100.00')
            ->assertJsonPath('data.discount_type', 'amount')
            ->assertJsonPath('data.details', ['min_buy' => '500.00', 'max_discount' => '100.00'])
            ->assertJsonPath('data.status', true)
            ->assertJsonPath('data.state', 'running')
            ->assertJsonPath('data.times_used', 0);
    }

    public function test_create_replace_and_delete(): void
    {
        $ids = Product::orderBy('id')->limit(3)->pluck('id')->all();

        $id = $this->postJson('/api/v1/coupons', [
            'type' => 'product_base', 'code' => ' kit-10 ', 'discount' => 10, 'discount_type' => 'percent',
            'start_date' => '2026-10-01T00:00:00+00:00', 'end_date' => '2026-12-01T00:00:00+00:00', 'status' => true,
            'product_ids' => $ids,
        ])
            ->assertCreated()
            ->assertJsonPath('data.code', 'KIT-10')
            ->assertJsonPath('data.details', ['product_ids' => $ids])
            // Stored in the app's timezone, but the same instant the client sent.
            ->assertJsonPath('data.start_date', fn (string $d) => Carbon::parse($d)->eq(Carbon::parse('2026-10-01T00:00:00+00:00')))
            ->json('data.id');

        // PUT is a full replacement; the type may be left out or repeated, but not changed.
        $this->putJson("/api/v1/coupons/{$id}", [
            'code' => 'KIT-10', 'discount' => 5, 'discount_type' => 'amount',
            'start_date' => '2026-10-01', 'end_date' => '2026-11-01', 'product_ids' => [$ids[0]],
        ])
            ->assertOk()
            ->assertJsonPath('data.discount', '5.00')
            ->assertJsonPath('data.status', false)
            ->assertJsonPath('data.details', ['product_ids' => [$ids[0]]]);

        $this->putJson("/api/v1/coupons/{$id}", [
            'type' => 'cart_base', 'code' => 'KIT-10', 'discount' => 5, 'discount_type' => 'amount',
            'start_date' => '2026-10-01', 'end_date' => '2026-11-01', 'min_buy' => 0,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('type');

        $this->deleteJson("/api/v1/coupons/{$id}")->assertNoContent();
        $this->assertDatabaseMissing('coupons', ['id' => $id]);
    }

    public function test_bad_input_is_422(): void
    {
        $this->postJson('/api/v1/coupons', [
            'type' => 'cart_base', 'code' => 'wowhello', 'discount' => 150, 'discount_type' => 'percent',
            'start_date' => '2026-10-02', 'end_date' => '2026-10-01',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['code', 'discount', 'end_date', 'min_buy']);

        $body = ['type' => ['cart_base'], 'code' => 'ARR', 'discount' => 5, 'discount_type' => 'amount',
            'start_date' => '2026-10-01', 'end_date' => '2026-11-01', 'min_buy' => 0];
        $this->postJson('/api/v1/coupons', $body)->assertUnprocessable()->assertJsonValidationErrors('type');

        $wow = Coupon::where('code', 'WOW100')->firstOrFail();
        $this->putJson("/api/v1/coupons/{$wow->id}", ['code' => 'WOW100'] + $body)->assertUnprocessable()->assertJsonValidationErrors('type');

        // An array start date used to crash the end date's after: rule with a 500.
        $this->postJson('/api/v1/coupons', ['type' => 'cart_base', 'start_date' => ['2026-10-01']] + $body)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('start_date');
    }

    public function test_money_in_details_is_sent_as_two_decimal_strings(): void
    {
        $this->postJson('/api/v1/coupons', [
            'type' => 'cart_base', 'code' => 'CAP', 'discount' => 10, 'discount_type' => 'percent',
            'start_date' => '2026-10-01', 'end_date' => '2026-11-01', 'min_buy' => '500', 'max_discount' => '75.5',
        ])
            ->assertCreated()
            ->assertJsonPath('data.details', ['min_buy' => '500.00', 'max_discount' => '75.50']);

        // The stored JSON keeps the numeric shape the storefront reads.
        $this->assertSame(['min_buy' => 500, 'max_discount' => 75.5], Coupon::where('code', 'CAP')->firstOrFail()->details);
    }

    public function test_flags_set_exactly_the_given_value(): void
    {
        $coupon = Coupon::where('code', 'ROLL15')->firstOrFail();

        foreach ([false, false, true] as $status) {
            $this->patchJson("/api/v1/coupons/{$coupon->id}/flags", ['status' => $status])
                ->assertOk()
                ->assertJsonPath('data.status', $status)
                ->assertJsonPath('data.state', $status ? 'running' : 'off');
        }

        $this->patchJson("/api/v1/coupons/{$coupon->id}/flags", [])->assertUnprocessable()->assertJsonValidationErrors('status');
    }

    public function test_deleting_a_used_coupon_is_409(): void
    {
        $coupon = Coupon::where('code', 'WOW100')->firstOrFail();
        CouponUsage::create(['coupon_id' => $coupon->id, 'user_id' => $this->admin->id]);

        $this->deleteJson("/api/v1/coupons/{$coupon->id}")
            ->assertStatus(409)
            ->assertJsonPath('message', 'Used 1 time - switch it off instead, so order history keeps its record.');

        $this->assertModelExists($coupon);
    }
}
