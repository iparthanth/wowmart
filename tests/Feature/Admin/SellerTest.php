<?php

namespace Tests\Feature\Admin;

use App\Enums\UserType;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use App\Services\SellerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SellerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::where('email', 'admin@wowmart.test')->firstOrFail();
    }

    private function seller(string $shop): User
    {
        return Shop::where('name', $shop)->firstOrFail()->user;
    }

    /** A seller with a shop and nothing else: no products, no sales, no history. */
    private function emptySeller(string $name = 'Fresh Stall'): User
    {
        $user = User::factory()->seller()->create(['name' => $name]);
        $shop = new Shop(['user_id' => $user->id, 'name' => $name]);
        $shop->forceFill(['slug' => str($name)->slug()])->save();

        return $user;
    }

    public function test_the_list_shows_every_seller_with_product_and_order_line_counts(): void
    {
        $this->actingAs($this->admin)->get('/admin/sellers')
            ->assertOk()
            ->assertSee('Circuit House')
            ->assertSee('circuit-house@sellers.wowmart.test')
            ->assertViewHas('sellers', function ($page) {
                $circuit = $page->firstWhere('name', 'Circuit House');
                $maker = $page->firstWhere('name', 'Maker Den');

                return $page->total() === 5
                    && (int) $circuit->products_count === 2 && (int) $circuit->order_lines_count === 1
                    && (int) $maker->products_count === 2 && (int) $maker->order_lines_count === 0;
            });
    }

    public function test_the_list_runs_the_same_number_of_queries_however_many_sellers_there_are(): void
    {
        $count = function () {
            DB::flushQueryLog();
            DB::enableQueryLog();
            $this->actingAs($this->admin)->get('/admin/sellers')->assertOk();

            return count(DB::getQueryLog());
        };

        $before = $count();
        $this->emptySeller('Stall One');
        $this->emptySeller('Stall Two');
        $this->emptySeller('Stall Three');

        $this->assertSame($before, $count());
    }

    public function test_the_pending_filter_shows_exactly_volt_street(): void
    {
        $this->actingAs($this->admin)->get('/admin/sellers?verification=pending')
            ->assertOk()
            ->assertViewHas('sellers', fn ($page) => $page->total() === 1 && $page->first()->shop->name === 'Volt Street');
    }

    public function test_search_and_status_filters_and_junk_values_are_ignored(): void
    {
        $this->actingAs($this->admin);

        $this->get('/admin/sellers?search=maker')
            ->assertViewHas('sellers', fn ($page) => $page->total() === 1 && $page->first()->name === 'Maker Den');
        $this->get('/admin/sellers?search=sensor-bazaar@')
            ->assertViewHas('sellers', fn ($page) => $page->total() === 1 && $page->first()->name === 'Sensor Bazaar');

        $this->seller('Receipt Roll Co')->forceFill(['banned' => true])->save();
        $this->get('/admin/sellers?status=banned')
            ->assertViewHas('sellers', fn ($page) => $page->total() === 1 && $page->first()->name === 'Receipt Roll Co');
        $this->get('/admin/sellers?status=active')->assertViewHas('sellers', fn ($page) => $page->total() === 4);

        $this->get('/admin/sellers?verification=nope&status[]=x&search[]=y')
            ->assertOk()
            ->assertViewHas('sellers', fn ($page) => $page->total() === 5);
    }

    public function test_customers_and_admins_are_not_sellers(): void
    {
        $customer = User::where('email', 'rafi@customers.wowmart.test')->firstOrFail();
        $this->actingAs($this->admin);

        foreach ([$customer, $this->admin] as $user) {
            $this->get("/admin/sellers/{$user->id}")->assertNotFound();
            $this->post("/admin/sellers/{$user->id}/verification", ['status' => 'approved'])->assertNotFound();
            $this->post("/admin/sellers/{$user->id}/toggle/banned")->assertNotFound();
            $this->delete("/admin/sellers/{$user->id}")->assertNotFound();
        }

        $this->get('/admin/sellers/abc')->assertNotFound();
        $this->assertFalse($this->admin->fresh()->banned);
        $this->assertNotNull($customer->fresh());
    }

    public function test_the_seller_page_shows_shop_products_and_order_lines(): void
    {
        $seller = $this->seller('Circuit House');
        $product = Product::where('user_id', $seller->id)->firstOrFail();
        $line = OrderDetail::where('seller_id', $seller->id)->with('order')->firstOrFail();

        $this->actingAs($this->admin)->get("/admin/sellers/{$seller->id}")
            ->assertOk()
            ->assertSee('Circuit House')
            ->assertSee(route('admin.products.edit', $product), false)
            ->assertSee($line->order->code)
            ->assertSee('Reject seller')
            ->assertDontSee('Approve seller')
            ->assertSee('Ban seller');
    }

    public function test_verification_moves_either_way_and_repeating_it_changes_nothing(): void
    {
        $volt = $this->seller('Volt Street');
        $this->actingAs($this->admin);

        $this->post("/admin/sellers/{$volt->id}/verification", ['status' => 'approved'])
            ->assertRedirect("/admin/sellers/{$volt->id}")
            ->assertSessionHas('status', 'Volt Street is now approved.');
        $this->assertSame('approved', $volt->shop->fresh()->verification_status);

        $stamp = $volt->shop->fresh()->updated_at;
        $this->travel(5)->minutes();
        $this->post("/admin/sellers/{$volt->id}/verification", ['status' => 'approved'])
            ->assertSessionHas('status', 'Volt Street is already approved.');
        $this->assertEquals($stamp, $volt->shop->fresh()->updated_at); // no write at all

        // Re-review: an approved shop can still be rejected, and a rejected one approved.
        $this->post("/admin/sellers/{$volt->id}/verification", ['status' => 'rejected'])
            ->assertSessionHas('status', 'Volt Street is now rejected.');
        $this->assertSame('rejected', $volt->shop->fresh()->verification_status);
        $this->post("/admin/sellers/{$volt->id}/verification", ['status' => 'approved']);
        $this->assertSame('approved', $volt->shop->fresh()->verification_status);
    }

    public function test_verification_accepts_only_approved_or_rejected(): void
    {
        $volt = $this->seller('Volt Street');
        $this->actingAs($this->admin)->from("/admin/sellers/{$volt->id}");

        foreach (['pending', 'maybe', ['approved']] as $bad) {
            $this->post("/admin/sellers/{$volt->id}/verification", ['status' => $bad])->assertSessionHasErrors('status');
        }

        $this->assertSame('pending', $volt->shop->fresh()->verification_status);
    }

    public function test_the_ban_switch_flips_and_set_flags_is_idempotent(): void
    {
        $seller = $this->seller('Maker Den');
        $this->actingAs($this->admin)->from("/admin/sellers/{$seller->id}");

        $this->post("/admin/sellers/{$seller->id}/toggle/banned")
            ->assertSessionHas('status', 'Maker Den is banned. The storefront hides its products.');
        $this->assertTrue($seller->fresh()->banned);

        $this->post("/admin/sellers/{$seller->id}/toggle/banned")->assertSessionHas('status', 'Maker Den is active again.');
        $this->assertFalse($seller->fresh()->banned);

        $service = app(SellerService::class);
        $service->setFlags($seller->fresh(), ['banned' => true]);
        $service->setFlags($seller->fresh(), ['banned' => true]);
        $this->assertTrue($seller->fresh()->banned);

        $this->post("/admin/sellers/{$seller->id}/toggle/approved")->assertNotFound(); // only "banned" is a flag
    }

    public function test_a_seller_with_products_or_order_lines_cannot_be_deleted(): void
    {
        $seller = $this->seller('Circuit House');

        $this->actingAs($this->admin)->from("/admin/sellers/{$seller->id}")
            ->delete("/admin/sellers/{$seller->id}")
            ->assertRedirect("/admin/sellers/{$seller->id}")
            ->assertSessionHas('error', 'Circuit House has 2 products and 1 order line; ban the seller instead.');

        $this->assertNotNull($seller->fresh());
        $this->assertNotNull($seller->shop->fresh());
    }

    public function test_a_seller_who_bought_as_a_customer_cannot_be_deleted(): void
    {
        $seller = $this->emptySeller();
        $order = new Order(['user_id' => $seller->id, 'shipping_address' => [], 'payment_type' => 'cash_on_delivery']);
        $order->forceFill(['code' => 'T-1'])->save();

        $this->actingAs($this->admin)->from('/admin/sellers')
            ->delete("/admin/sellers/{$seller->id}")
            ->assertSessionHas('error', 'Fresh Stall has orders, refunds, reviews or coupons on record; ban the seller instead.');

        $this->assertNotNull($order->fresh());
    }

    public function test_a_seller_with_nothing_is_deleted_with_the_shop(): void
    {
        $seller = $this->emptySeller();
        $seller->createToken('storefront');
        $shopId = $seller->shop->id;

        $this->actingAs($this->admin)->delete("/admin/sellers/{$seller->id}")
            ->assertRedirect('/admin/sellers')
            ->assertSessionHas('status', 'Fresh Stall deleted.');

        $this->assertNull($seller->fresh());
        $this->assertNull(Shop::find($shopId));
        $this->assertDatabaseMissing('personal_access_tokens', ['tokenable_id' => $seller->id]);
        $this->assertSame(5, User::where('user_type', UserType::Seller)->count());
    }
}
