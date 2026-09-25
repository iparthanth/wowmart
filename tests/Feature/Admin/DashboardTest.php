<?php

namespace Tests\Feature\Admin;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Leans on the seed data being deterministic: the same seed always produces
 * 2 pending orders, 1 sold-out product and 2 low on stock, so the dashboard
 * can be checked against exact numbers rather than "something non-zero".
 */
class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function admin(): User
    {
        return User::where('email', 'admin@wowmart.test')->firstOrFail();
    }

    public function test_the_work_queue_shows_exact_counts_from_the_seed(): void
    {
        $html = $this->actingAs($this->admin())->get('/admin')->assertOk()->getContent();

        foreach (['Pending orders' => 2, 'Out of stock' => 1, 'Low on stock' => 2, 'Missing images' => 1] as $label => $count) {
            $this->assertMatchesRegularExpression(
                '/'.preg_quote($label, '/').'\s*<\/span>\s*<span class="count">'.$count.'<\/span>/',
                $html,
                "Expected {$label} = {$count}",
            );
        }
    }

    public function test_a_clear_queue_says_so_instead_of_showing_a_zero_with_a_spark(): void
    {
        Order::query()->update(['delivery_status' => 'delivered', 'payment_status' => 'paid']);

        $this->actingAs($this->admin())->get('/admin')
            ->assertOk()
            ->assertSee('Nothing to do');
    }

    public function test_the_chart_and_latest_orders_render(): void
    {
        $latest = Order::latest('id')->first();

        $this->actingAs($this->admin())->get('/admin')
            ->assertOk()
            ->assertSee('Order value, last 14 days')
            ->assertSee('View as table')
            ->assertSee($latest->code);
    }

    public function test_cancelled_orders_are_left_out_of_order_value(): void
    {
        $counted = (float) Order::where('delivery_status', '!=', 'cancelled')->sum('grand_total');

        $this->actingAs($this->admin())->get('/admin')
            ->assertSee('৳'.number_format($counted, 2));
    }
}
