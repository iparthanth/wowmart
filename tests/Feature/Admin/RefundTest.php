<?php

namespace Tests\Feature\Admin;

use App\Enums\RefundStatus;
use App\Models\BusinessSetting;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\RefundRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RefundTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->actingAs(User::where('email', 'admin@wowmart.test')->firstOrFail());
    }

    private function refundIn(RefundStatus $status): RefundRequest
    {
        return RefundRequest::where('refund_status', $status)->firstOrFail();
    }

    public function test_the_seed_has_one_request_per_status_on_delivered_lines(): void
    {
        $this->assertSame(3, RefundRequest::count());

        foreach (RefundRequest::with('orderDetail', 'order')->get() as $refund) {
            $line = $refund->orderDetail;
            $this->assertSame('delivered', $refund->order->delivery_status->value);
            $this->assertSame($refund->order->user_id, $refund->user_id);
            $this->assertSame($line->seller_id, $refund->seller_id);
            $this->assertSame(number_format((float) $line->price * $line->quantity, 2, '.', ''), $refund->refund_amount);
        }
    }

    public function test_tabs_show_counts_and_default_to_pending(): void
    {
        $pending = $this->refundIn(RefundStatus::Pending);
        $approved = $this->refundIn(RefundStatus::Approved);

        $html = $this->get('/admin/refund-requests')
            ->assertOk()
            ->assertSee($pending->order->code)
            ->assertDontSee($approved->order->code)
            ->assertSee('Approve refund')
            ->getContent();

        foreach (['Pending', 'Approved', 'Rejected'] as $label) {
            $this->assertMatchesRegularExpression('/'.$label.' <span class="num">1<\/span>/', $html, "{$label} tab should count 1");
        }

        $this->get('/admin/refund-requests?status=approved')
            ->assertOk()
            ->assertSee($approved->order->code)
            ->assertDontSee($pending->order->code)
            ->assertDontSee('Approve refund');

        // Unknown values fall back to the pending tab instead of failing.
        $this->get('/admin/refund-requests?status=nonsense')->assertOk()->assertSee($pending->order->code);
        $this->get('/admin/refund-requests?status[]=x&search[]=y')->assertOk()->assertSee($pending->order->code);
    }

    public function test_search_matches_order_code_and_customer_name(): void
    {
        $pending = $this->refundIn(RefundStatus::Pending);

        $this->get('/admin/refund-requests?search='.$pending->order->code)->assertOk()->assertSee($pending->order->code);
        $this->get('/admin/refund-requests?search='.urlencode($pending->customer->name))->assertOk()->assertSee($pending->order->code);
        $this->get('/admin/refund-requests?search=nobody-by-this-name')->assertOk()->assertDontSee($pending->order->code)->assertSee('Nothing matches');
    }

    public function test_approving_records_the_decision_and_touches_no_stock_or_payment(): void
    {
        $refund = $this->refundIn(RefundStatus::Pending);
        $order = $refund->order;
        $stock = Product::sum('current_stock');
        $variantStock = ProductStock::sum('qty');

        $this->from('/admin/refund-requests')
            ->post("/admin/refund-requests/{$refund->id}/approve")
            ->assertRedirect('/admin/refund-requests')
            ->assertSessionHas('status');

        $this->assertSame(RefundStatus::Approved, $refund->fresh()->refund_status);
        $this->assertSame($order->payment_status, $order->fresh()->payment_status);
        $this->assertEquals($stock, Product::sum('current_stock'));
        $this->assertEquals($variantStock, ProductStock::sum('qty'));
    }

    public function test_rejecting_records_the_decision(): void
    {
        $refund = $this->refundIn(RefundStatus::Pending);

        $this->from('/admin/refund-requests')
            ->post("/admin/refund-requests/{$refund->id}/reject")
            ->assertRedirect('/admin/refund-requests')
            ->assertSessionHas('status');

        $this->assertSame(RefundStatus::Rejected, $refund->fresh()->refund_status);
    }

    public function test_a_second_decision_is_refused(): void
    {
        $refund = $this->refundIn(RefundStatus::Pending);
        $this->post("/admin/refund-requests/{$refund->id}/approve");

        // A double-click, or a second admin with a stale page.
        $this->from('/admin/refund-requests')
            ->post("/admin/refund-requests/{$refund->id}/reject")
            ->assertRedirect('/admin/refund-requests')
            ->assertSessionHas('error', fn (string $m) => str_starts_with($m, 'This refund was already approved.'));

        $this->assertSame(RefundStatus::Approved, $refund->fresh()->refund_status);

        $this->post('/admin/refund-requests/'.$this->refundIn(RefundStatus::Rejected)->id.'/approve')
            ->assertSessionHas('error', fn (string $m) => str_starts_with($m, 'This refund was already rejected.'));
    }

    public function test_the_list_survives_deleted_products(): void
    {
        $pending = $this->refundIn(RefundStatus::Pending);
        $approved = $this->refundIn(RefundStatus::Approved);

        // The product is gone entirely (the line's key is nulled by the FK)...
        Product::whereKey($pending->orderDetail->product_id)->delete();
        // ...and a line whose product reference was cleared another way.
        $approved->orderDetail->forceFill(['product_id' => null])->save();

        $this->get('/admin/refund-requests')->assertOk()->assertSee('Deleted product')->assertSee($pending->order->code);
        $this->get('/admin/refund-requests?status=approved')->assertOk()->assertSee('Deleted product');
    }

    public function test_settings_show_the_default_then_the_saved_value(): void
    {
        $this->get('/admin/refund-requests/settings')->assertOk()->assertSee('value="7"', false);

        $this->put('/admin/refund-requests/settings', ['refund_request_time' => 14])
            ->assertRedirect('/admin/refund-requests/settings')
            ->assertSessionHas('status');

        $this->assertSame('14', BusinessSetting::get('refund_request_time'));
        $this->get('/admin/refund-requests/settings')->assertOk()->assertSee('value="14"', false);
    }

    public static function badWindows(): array
    {
        return [
            'zero' => [0],
            'over a year' => [366],
            'fraction' => ['1.5'],
            'text' => ['week'],
            'array' => [[7]],
            'missing' => [null],
        ];
    }

    #[DataProvider('badWindows')]
    public function test_settings_refuse_invalid_windows(mixed $value): void
    {
        $this->from('/admin/refund-requests/settings')
            ->put('/admin/refund-requests/settings', ['refund_request_time' => $value])
            ->assertRedirect('/admin/refund-requests/settings')
            ->assertSessionHasErrors('refund_request_time');

        $this->assertNull(BusinessSetting::get('refund_request_time'));
    }

    public function test_a_long_reason_is_shortened_with_the_full_text_behind_details(): void
    {
        $refund = $this->refundIn(RefundStatus::Pending);
        $refund->forceFill(['reason' => str_repeat('a', 130).'END'])->save();

        $this->get('/admin/refund-requests')->assertOk()->assertSee('<details>', false)->assertSee(str_repeat('a', 130).'END');
    }
}
