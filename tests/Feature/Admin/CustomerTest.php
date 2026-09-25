<?php

namespace Tests\Feature\Admin;

use App\Enums\DeliveryStatus;
use App\Enums\UserType;
use App\Models\Address;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::where('email', 'admin@wowmart.test')->firstOrFail();
    }

    private function customer(string $name): User
    {
        return User::where('email', strtolower($name).'@customers.wowmart.test')->firstOrFail();
    }

    private function valid(array $overrides = []): array
    {
        return [
            'name' => 'Farhana Akter',
            'email' => 'farhana@example.com',
            'country_code' => '+880',
            'phone' => '01811-222 333',
            'password' => 'secret-pass',
            'password_confirmation' => 'secret-pass',
            ...$overrides,
        ];
    }

    public function test_admin_and_seller_ids_do_not_resolve_to_a_customer(): void
    {
        $seller = User::where('user_type', UserType::Seller)->firstOrFail();

        foreach ([$this->admin, $seller] as $user) {
            $this->actingAs($this->admin)->get("/admin/customers/{$user->id}")->assertNotFound();
            $this->actingAs($this->admin)->get("/admin/customers/{$user->id}/edit")->assertNotFound();
            $this->actingAs($this->admin)->put("/admin/customers/{$user->id}", $this->valid())->assertNotFound();
            $this->actingAs($this->admin)->post("/admin/customers/{$user->id}/toggle/banned", ['banned' => '1'])->assertNotFound();
            $this->actingAs($this->admin)->delete("/admin/customers/{$user->id}")->assertNotFound();
        }

        $this->assertFalse($this->admin->fresh()->banned);
        $this->assertDatabaseHas('users', ['id' => $seller->id]);
    }

    public function test_the_list_holds_only_customers_and_filters_them(): void
    {
        User::factory()->create(['name' => 'Verified Vera']);                        // verified, active
        User::factory()->unverified()->banned()->create(['name' => 'Banned Bashir']);

        $total = fn (string $query) => $this->actingAs($this->admin)->get('/admin/customers'.$query)
            ->assertOk()->viewData('customers')->total();

        $this->assertSame(5, $total(''));                          // 3 seeded + 2 here; no admin, no sellers
        $this->assertSame(1, $total('?verification=verified'));
        $this->assertSame(4, $total('?verification=unverified'));
        $this->assertSame(1, $total('?status=banned'));
        $this->assertSame(4, $total('?status=active'));
        $this->assertSame(0, $total('?status=banned&verification=verified'));
        $this->assertSame(1, $total('?search=NUSRAT'));
        $this->assertSame(5, $total('?status=nonsense&verification[]=x&search[]=y')); // ignored, not a 500

        $this->actingAs($this->admin)->get('/admin/customers?status=banned')
            ->assertSee('Banned Bashir')->assertDontSee('Verified Vera');
    }

    public function test_search_finds_a_phone_number(): void
    {
        $this->customer('Tanvir')->forceFill(['phone' => '1711000999', 'country_code' => '+880'])->save();

        $this->actingAs($this->admin)->get('/admin/customers?search=000999')
            ->assertOk()->assertSee('Tanvir Hasan')->assertDontSee('Rafi Ahmed');
    }

    public function test_total_spent_leaves_out_cancelled_orders(): void
    {
        $nusrat = $this->customer('Nusrat');
        $all = (float) Order::where('user_id', $nusrat->id)->sum('grand_total');
        $kept = (float) Order::where('user_id', $nusrat->id)->where('delivery_status', '!=', DeliveryStatus::Cancelled)->sum('grand_total');
        $this->assertGreaterThan($kept, $all, 'the seed gives Nusrat one cancelled order');

        $row = $this->actingAs($this->admin)->get('/admin/customers?search=nusrat')
            ->assertOk()
            ->assertSee('৳'.number_format($kept, 2))
            ->viewData('customers')->first();

        $this->assertSame(5, $row->orders_count);
        $this->assertSame(1402.5, round((float) $row->total_spent, 2));
    }

    public function test_sort_by_spent_puts_the_biggest_spender_first(): void
    {
        $names = $this->actingAs($this->admin)->get('/admin/customers?sort=spent')
            ->viewData('customers')->pluck('name')->all();

        $this->assertSame(['Tanvir Hasan', 'Nusrat Jahan', 'Rafi Ahmed'], $names);
    }

    public function test_the_list_does_not_query_per_row(): void
    {
        $count = function () {
            DB::flushQueryLog();
            DB::enableQueryLog();
            $this->actingAs($this->admin)->get('/admin/customers')->assertOk();

            return count(DB::getQueryLog());
        };

        $few = $count();
        User::factory()->count(15)->create();

        $this->assertSame($few, $count());
    }

    public function test_the_profile_shows_orders_addresses_and_counts(): void
    {
        $rafi = $this->customer('Rafi');
        Address::create(['user_id' => $rafi->id, 'name' => 'Rafi at work', 'phone' => '01700000000', 'address' => 'Agrabad C/A', 'city' => 'Chattogram']);
        Wishlist::create(['user_id' => $rafi->id, 'product_id' => Product::orderBy('id')->value('id')]);
        $latest = Order::where('user_id', $rafi->id)->latest('id')->firstOrFail();

        $this->actingAs($this->admin)->get("/admin/customers/{$rafi->id}")
            ->assertOk()
            ->assertSee($latest->code)
            ->assertSee(route('admin.orders.show', $latest))
            ->assertSee('Agrabad C/A')
            ->assertViewHas('customer', fn (User $c) => $c->orders_count === 4 && $c->wishlists_count === 1
                && $c->reviews_count === Review::where('user_id', $rafi->id)->count())
            ->assertSee('Ban account')
            ->assertDontSee('Delete customer'); // has orders
    }

    public function test_a_customer_is_added_with_a_normalised_phone_and_no_verification(): void
    {
        $this->actingAs($this->admin)
            ->post('/admin/customers', $this->valid(['user_type' => 'admin', 'banned' => '1', 'email_verified_at' => now()->toDateTimeString()]))
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $new = User::where('email', 'farhana@example.com')->firstOrFail();
        $this->assertSame(UserType::Customer, $new->user_type);
        $this->assertFalse($new->banned);
        $this->assertNull($new->email_verified_at);
        $this->assertSame('1811222333', $new->phone); // the storefront's stored form
        $this->assertSame('+880', $new->country_code);
        $this->assertTrue(Hash::check('secret-pass', $new->password));
    }

    public function test_email_or_phone_is_required_and_both_must_be_unique(): void
    {
        $this->customer('Tanvir')->forceFill(['phone' => '1711000999', 'country_code' => '+880'])->save();
        $post = fn (array $data) => $this->actingAs($this->admin)->from('/admin/customers/create')->post('/admin/customers', $this->valid($data));

        $post(['email' => '', 'phone' => ''])->assertSessionHasErrors('email');
        $post(['email' => 'RAFI@Customers.WowMart.test'])->assertSessionHasErrors('email');   // case-insensitive
        $post(['email' => null, 'phone' => '+880 1711-000999'])->assertSessionHasErrors('phone'); // same number, other spelling
        $post(['phone' => '12ab'])->assertSessionHasErrors('phone');
        $post(['password_confirmation' => 'different'])->assertSessionHasErrors('password');
        $post(['password' => 'short', 'password_confirmation' => 'short'])->assertSessionHasErrors('password');
        $post(['name' => str_repeat('x', 121)])->assertSessionHasErrors('name');
        $this->assertDatabaseMissing('users', ['name' => 'Farhana Akter']);

        // Phone only is enough, and email only is enough.
        $post(['email' => null])->assertSessionHasNoErrors();
        $post(['email' => 'only@example.com', 'phone' => null, 'name' => 'Email Only'])->assertSessionHasNoErrors();
        $this->assertNull(User::where('email', 'only@example.com')->value('country_code'));
    }

    public function test_editing_keeps_the_password_when_left_blank(): void
    {
        $rafi = $this->customer('Rafi');

        $this->actingAs($this->admin)->put("/admin/customers/{$rafi->id}", [
            'name' => 'Rafi Ahmed Khan', 'email' => 'RAFI@customers.wowmart.test', 'phone' => '', 'password' => '', 'password_confirmation' => '',
        ])->assertSessionHasNoErrors()->assertRedirect("/admin/customers/{$rafi->id}");

        $rafi->refresh();
        $this->assertSame('Rafi Ahmed Khan', $rafi->name);
        $this->assertTrue(Hash::check('password', $rafi->password));

        $this->actingAs($this->admin)->put("/admin/customers/{$rafi->id}", $this->valid(['email' => 'rafi@customers.wowmart.test', 'phone' => null]))
            ->assertSessionHasNoErrors();
        $this->assertTrue(Hash::check('secret-pass', $rafi->fresh()->password));
    }

    public function test_banning_is_idempotent_and_reversible(): void
    {
        $rafi = $this->customer('Rafi');

        foreach ([1, 2] as $_) {
            $this->actingAs($this->admin)->post("/admin/customers/{$rafi->id}/toggle/banned", ['banned' => '1'])->assertSessionHas('status');
            $this->assertTrue($rafi->fresh()->banned);
        }

        $this->actingAs($this->admin)->post("/admin/customers/{$rafi->id}/toggle/banned", ['banned' => 'maybe'])->assertSessionHasErrors('banned');
        $this->assertTrue($rafi->fresh()->banned);

        $this->actingAs($this->admin)->post("/admin/customers/{$rafi->id}/toggle/banned", ['banned' => '0']);
        $this->assertFalse($rafi->fresh()->banned);
    }

    public function test_a_customer_with_orders_cannot_be_deleted(): void
    {
        $nusrat = $this->customer('Nusrat');

        $this->actingAs($this->admin)->delete("/admin/customers/{$nusrat->id}")
            ->assertSessionHas('error', 'Nusrat Jahan has 5 orders; order history must stay. Ban the account instead.');

        $this->assertDatabaseHas('users', ['id' => $nusrat->id]);
        $this->assertSame(5, Order::where('user_id', $nusrat->id)->count());
    }

    public function test_deleting_a_customer_without_orders_recomputes_ratings(): void
    {
        $customer = User::factory()->create();
        $seeded = Review::where('status', true)->orderBy('id')->firstOrFail();          // Rafi's 5-star review
        $shared = Product::findOrFail($seeded->product_id);
        $onlyTheirs = Product::whereDoesntHave('reviews')->orderBy('id')->firstOrFail();

        foreach ([$shared, $onlyTheirs] as $product) {
            (new Review(['product_id' => $product->id, 'user_id' => $customer->id, 'rating' => 1]))->save();
        }
        $shared->forceFill(['rating' => 3])->save();       // (5 + 1) / 2 while the review exists
        $onlyTheirs->forceFill(['rating' => 1])->save();
        Address::create(['user_id' => $customer->id, 'name' => 'Home', 'phone' => '01700000000', 'address' => 'Road 1', 'city' => 'Dhaka']);
        Wishlist::create(['user_id' => $customer->id, 'product_id' => $shared->id]);
        $customer->createToken('app');

        $this->actingAs($this->admin)->delete("/admin/customers/{$customer->id}")
            ->assertRedirect('/admin/customers')
            ->assertSessionHas('status');

        $this->assertDatabaseMissing('users', ['id' => $customer->id]);
        $this->assertSame(0, Review::where('user_id', $customer->id)->count());
        $this->assertDatabaseMissing('addresses', ['user_id' => $customer->id]);
        $this->assertDatabaseMissing('wishlists', ['user_id' => $customer->id]);
        $this->assertDatabaseMissing('personal_access_tokens', ['tokenable_id' => $customer->id]);
        $this->assertSame((float) $seeded->rating, (float) $shared->fresh()->rating);
        $this->assertSame(0.0, (float) $onlyTheirs->fresh()->rating);
    }

    public function test_the_database_refuses_to_delete_a_customer_with_orders(): void
    {
        $nusrat = $this->customer('Nusrat');

        try {
            DB::table('users')->where('id', $nusrat->id)->delete();
            $this->fail('Deleting a customer with orders must be refused by the foreign key.');
        } catch (QueryException) {
            // expected: orders.user_id is restrictOnDelete
        }

        $this->assertSame(5, Order::where('user_id', $nusrat->id)->count());
    }

    public function test_the_forms_render(): void
    {
        $rafi = $this->customer('Rafi');

        $this->actingAs($this->admin)->get('/admin/customers/create')->assertOk()->assertSee('does not verify their email');
        $this->actingAs($this->admin)->get("/admin/customers/{$rafi->id}/edit")->assertOk()->assertSee('Leave blank to keep the current password.')
            ->assertSee('rafi@customers.wowmart.test');
    }
}
