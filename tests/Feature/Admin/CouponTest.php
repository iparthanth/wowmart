<?php

namespace Tests\Feature\Admin;

use App\Enums\CouponType;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CouponTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::where('email', 'admin@wowmart.test')->firstOrFail();
    }

    private function base(array $overrides = []): array
    {
        return $overrides + [
            'discount' => '10',
            'discount_type' => 'amount',
            'start_date' => '2026-10-01T09:00',
            'end_date' => '2026-10-31T23:00',
            'status' => '1',
        ];
    }

    private function use(Coupon $coupon, int $times): void
    {
        foreach (range(1, $times) as $_) {
            CouponUsage::create(['coupon_id' => $coupon->id, 'user_id' => $this->admin->id]);
        }
    }

    public function test_the_list_shows_state_discount_and_times_used(): void
    {
        $roll = Coupon::where('code', 'ROLL15')->firstOrFail();
        $this->use($roll, 2);
        Coupon::where('code', 'WOW100')->update(['status' => false]);

        $this->actingAs($this->admin)->get('/admin/coupon')
            ->assertOk()
            ->assertSeeInOrder(['ROLL15', 'Product Base', '15%', 'Running'])
            ->assertSeeInOrder(['WOW100', 'Cart Base', '৳100.00', 'Switched off'])
            ->assertViewHas('coupons', fn ($page) => $page->firstWhere('code', 'ROLL15')->usages_count === 2);
    }

    public function test_state_follows_the_switch_and_the_dates(): void
    {
        $c = new Coupon(['status' => true, 'start_date' => now()->addDay(), 'end_date' => now()->addDays(2)]);
        $this->assertSame('scheduled', $c->state());

        $c->start_date = now()->subDays(2);
        $c->end_date = now()->subDay();
        $this->assertSame('expired', $c->state());

        $c->end_date = now()->addDay();
        $this->assertSame('running', $c->state());

        $c->status = false;
        $this->assertSame('off', $c->state());
    }

    public function test_create_starts_with_a_type_chooser_and_unknown_types_fall_back_to_it(): void
    {
        $this->actingAs($this->admin)->get('/admin/coupon/create')
            ->assertOk()
            ->assertViewIs('admin.coupons.choose')
            ->assertSee('/admin/coupon/create?type=cart_base', false);

        $this->actingAs($this->admin)->get('/admin/coupon/create?type=bogus')->assertViewIs('admin.coupons.choose');
        $this->actingAs($this->admin)->get('/admin/coupon/create?type[]=cart_base')->assertOk()->assertViewIs('admin.coupons.choose');

        $this->actingAs($this->admin)->get('/admin/coupon/create?type=product_base')
            ->assertOk()
            ->assertViewIs('admin.coupons.form')
            ->assertSee('name="product_ids[]"', false);

        $this->actingAs($this->admin)->get('/admin/coupon/create?type=welcome_base')->assertSee('name="validation_days"', false);
    }

    public function test_a_product_coupon_stores_only_its_product_ids(): void
    {
        $ids = Product::orderBy('id')->limit(2)->pluck('id')->all();

        $this->actingAs($this->admin)
            ->post('/admin/coupon', $this->base([
                'type' => 'product_base', 'code' => 'PARTS5', 'product_ids' => array_map('strval', $ids),
                'min_buy' => '999', // belongs to another type: must not leak into details
            ]))
            ->assertRedirect('/admin/coupon')
            ->assertSessionHas('status');

        $coupon = Coupon::where('code', 'PARTS5')->firstOrFail();
        $this->assertSame(CouponType::ProductBase, $coupon->type);
        $this->assertSame(['product_ids' => $ids], $coupon->details);
        $this->assertSame($this->admin->id, $coupon->user_id);
        $this->assertTrue($coupon->status);
    }

    public function test_a_cart_coupon_stores_min_buy_and_max_discount_as_numbers(): void
    {
        $this->actingAs($this->admin)
            ->post('/admin/coupon', $this->base(['type' => 'cart_base', 'code' => 'CART50', 'discount_type' => 'percent', 'min_buy' => '500', 'max_discount' => '75.5']))
            ->assertSessionHasNoErrors();

        $this->assertSame(['min_buy' => 500, 'max_discount' => 75.5], Coupon::where('code', 'CART50')->firstOrFail()->details);

        $this->actingAs($this->admin)
            ->post('/admin/coupon', $this->base(['type' => 'cart_base', 'code' => 'CARTNOCAP', 'min_buy' => '0']))
            ->assertSessionHasNoErrors();

        $this->assertSame(['min_buy' => 0, 'max_discount' => null], Coupon::where('code', 'CARTNOCAP')->firstOrFail()->details);
    }

    public function test_a_welcome_coupon_stores_validation_days_and_min_buy(): void
    {
        $this->actingAs($this->admin)
            ->post('/admin/coupon', $this->base(['type' => 'welcome_base', 'code' => 'HELLO2', 'validation_days' => '14', 'min_buy' => '200']))
            ->assertSessionHasNoErrors();

        $this->assertSame(['validation_days' => 14, 'min_buy' => 200], Coupon::where('code', 'HELLO2')->firstOrFail()->details);
    }

    public function test_type_specific_fields_are_required(): void
    {
        $this->actingAs($this->admin)->post('/admin/coupon', $this->base(['type' => 'product_base', 'code' => 'A1', 'product_ids' => []]))
            ->assertSessionHasErrors('product_ids');
        $this->actingAs($this->admin)->post('/admin/coupon', $this->base(['type' => 'product_base', 'code' => 'A2', 'product_ids' => [999999]]))
            ->assertSessionHasErrors('product_ids.0');
        $this->actingAs($this->admin)->post('/admin/coupon', $this->base(['type' => 'cart_base', 'code' => 'A3', 'max_discount' => '0']))
            ->assertSessionHasErrors(['min_buy', 'max_discount']);
        $this->actingAs($this->admin)->post('/admin/coupon', $this->base(['type' => 'welcome_base', 'code' => 'A4', 'validation_days' => '366']))
            ->assertSessionHasErrors('validation_days');
        $this->actingAs($this->admin)->post('/admin/coupon', $this->base(['type' => 'mystery', 'code' => 'A5']))
            ->assertSessionHasErrors('type');

        $this->assertSame(3, Coupon::count());
    }

    public function test_the_code_is_trimmed_uppercased_and_unique_in_any_case(): void
    {
        $this->actingAs($this->admin)
            ->post('/admin/coupon', $this->base(['type' => 'cart_base', 'code' => '  eid-sale-26 ', 'min_buy' => '0']))
            ->assertSessionHasNoErrors();
        $this->assertDatabaseHas('coupons', ['code' => 'EID-SALE-26']);

        $this->actingAs($this->admin)->post('/admin/coupon', $this->base(['type' => 'cart_base', 'code' => 'wow100', 'min_buy' => '0']))
            ->assertSessionHasErrors('code');
        // Rows written elsewhere (storefront, SQL) may be lowercase; the check must still catch them.
        DB::table('coupons')->where('code', 'WOW100')->update(['code' => 'wow100']);
        $this->actingAs($this->admin)->post('/admin/coupon', $this->base(['type' => 'cart_base', 'code' => 'WOW100', 'min_buy' => '0']))
            ->assertSessionHasErrors('code');
        $this->actingAs($this->admin)->post('/admin/coupon', $this->base(['type' => 'cart_base', 'code' => 'two words', 'min_buy' => '0']))
            ->assertSessionHasErrors('code');
        $this->actingAs($this->admin)->post('/admin/coupon', $this->base(['type' => 'cart_base', 'code' => str_repeat('X', 33), 'min_buy' => '0']))
            ->assertSessionHasErrors('code');
    }

    public function test_discount_limits_and_date_order(): void
    {
        $cart = ['type' => 'cart_base', 'code' => 'LIMITS', 'min_buy' => '0'];

        $this->actingAs($this->admin)->post('/admin/coupon', $this->base($cart + ['discount_type' => 'percent', 'discount' => '101']))
            ->assertSessionHasErrors('discount');
        $this->actingAs($this->admin)->post('/admin/coupon', $this->base($cart + ['discount' => '0']))
            ->assertSessionHasErrors('discount');
        // Would round to 0.00 in the decimal(20,2) column.
        $this->actingAs($this->admin)->post('/admin/coupon', $this->base($cart + ['discount' => '0.001']))
            ->assertSessionHasErrors('discount');
        // MySQL TIMESTAMP ends in January 2038.
        $this->actingAs($this->admin)->post('/admin/coupon', $this->base($cart + ['end_date' => '2040-01-01T00:00']))
            ->assertSessionHasErrors('end_date');
        $this->actingAs($this->admin)->post('/admin/coupon', $this->base($cart + ['start_date' => '1960-01-01T00:00']))
            ->assertSessionHasErrors('start_date');
        $this->actingAs($this->admin)->post('/admin/coupon', $this->base($cart + ['start_date' => '2026-10-31T09:00', 'end_date' => '2026-10-01T09:00']))
            ->assertSessionHasErrors('end_date');
        $this->actingAs($this->admin)->post('/admin/coupon', $this->base($cart + ['discount' => '250']))
            ->assertSessionHasNoErrors(); // a flat amount over 100 is fine
    }

    public function test_edit_keeps_the_type_and_rewrites_details(): void
    {
        $coupon = Coupon::where('code', 'WOW100')->firstOrFail();

        $this->actingAs($this->admin)->get("/admin/coupon/{$coupon->id}/edit")
            ->assertOk()
            ->assertSee('Save changes')
            ->assertDontSee('name="type"', false);

        $this->actingAs($this->admin)
            ->put("/admin/coupon/{$coupon->id}", $this->base(['code' => 'wow100', 'min_buy' => '800']))
            ->assertRedirect("/admin/coupon/{$coupon->id}/edit");

        $coupon->refresh();
        $this->assertSame(CouponType::CartBase, $coupon->type);
        $this->assertSame(['min_buy' => 800, 'max_discount' => null], $coupon->details);

        // An unticked "Active" box switches it off.
        $this->actingAs($this->admin)
            ->put("/admin/coupon/{$coupon->id}", array_diff_key($this->base(['code' => 'WOW100', 'min_buy' => '800']), ['status' => 1]))
            ->assertSessionHasNoErrors();
        $this->assertFalse($coupon->fresh()->status);

        $this->actingAs($this->admin)
            ->put("/admin/coupon/{$coupon->id}", $this->base(['type' => 'welcome_base', 'code' => 'WOW100', 'validation_days' => '5']))
            ->assertSessionHasErrors('type');
        $this->assertSame(CouponType::CartBase, $coupon->fresh()->type);
    }

    public function test_a_used_coupon_cannot_be_deleted(): void
    {
        $coupon = Coupon::where('code', 'WOWHELLO')->firstOrFail();
        $this->use($coupon, 3);

        $this->actingAs($this->admin)
            ->from('/admin/coupon')
            ->delete("/admin/coupon/{$coupon->id}")
            ->assertRedirect('/admin/coupon')
            ->assertSessionHas('error', 'Used 3 times - switch it off instead, so order history keeps its record.');

        $this->assertModelExists($coupon);
    }

    public function test_an_unused_coupon_is_deleted(): void
    {
        $coupon = Coupon::where('code', 'ROLL15')->firstOrFail();

        $this->actingAs($this->admin)->delete("/admin/coupon/{$coupon->id}")->assertRedirect('/admin/coupon');

        $this->assertModelMissing($coupon);
    }

    public function test_the_status_switch_flips_it(): void
    {
        $coupon = Coupon::where('code', 'WOW100')->firstOrFail();

        $this->actingAs($this->admin)->post("/admin/coupon/{$coupon->id}/toggle/status")->assertSessionHas('status');
        $this->assertFalse($coupon->fresh()->status);

        $this->actingAs($this->admin)->post("/admin/coupon/{$coupon->id}/toggle/status");
        $this->assertTrue($coupon->fresh()->status);

        $this->actingAs($this->admin)->post("/admin/coupon/{$coupon->id}/toggle/code")->assertNotFound();
    }
}
