<?php

namespace Tests\Feature\Api\V1;

use App\Models\FlashDeal;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class FlashDealApiTest extends TestCase
{
    use RefreshDatabase;

    private FlashDeal $weekend;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Sanctum::actingAs(User::where('email', 'admin@wowmart.test')->firstOrFail());
        $this->weekend = FlashDeal::where('slug', 'weekend-maker-sale')->firstOrFail();
    }

    public function test_list_and_show(): void
    {
        $this->getJson('/api/v1/flash-deals')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonStructure(['data' => [['id', 'title', 'slug', 'page_url', 'start_date', 'end_date', 'status', 'featured', 'background_color', 'text_color', 'banner', 'state', 'product_count']], 'links', 'meta'])
            ->assertJsonMissingPath('data.0.products');

        $first = $this->weekend->products()->orderBy('products.id')->first();

        $this->getJson("/api/v1/flash-deals/{$this->weekend->id}")
            ->assertOk()
            ->assertJsonPath('data.slug', 'weekend-maker-sale')
            ->assertJsonPath('data.page_url', config('services.storefront.url').'/flash-deal/weekend-maker-sale')
            ->assertJsonPath('data.status', true)
            ->assertJsonPath('data.featured', true)
            ->assertJsonPath('data.state', 'running')
            ->assertJsonPath('data.banner', null)
            ->assertJsonFragment(['id' => $first->id, 'name' => $first->name, 'unit_price' => $first->unit_price, 'discount' => '10.00', 'discount_type' => 'percent']);
    }

    public function test_create_replace_and_delete(): void
    {
        [$a, $b] = Product::doesntHave('flashDeals')->where('unit_price', '>', 10)->orderBy('id')->limit(2)->get();

        $id = $this->postJson('/api/v1/flash-deals', [
            'title' => 'API Deal', 'start_date' => '2026-10-01T00:00:00+00:00', 'end_date' => '2026-10-08T00:00:00+00:00',
            'status' => true, 'featured' => true, 'background_color' => '#000000', 'text_color' => '#FFFFFF',
            'products' => [['id' => $a->id, 'discount' => 5, 'discount_type' => 'amount'], ['id' => $b->id, 'discount' => 20, 'discount_type' => 'percent']],
        ])
            ->assertCreated()
            ->assertJsonPath('data.slug', 'api-deal')
            ->assertJsonPath('data.featured', true)
            ->assertJsonPath('data.start_date', fn (string $d) => Carbon::parse($d)->eq(Carbon::parse('2026-10-01T00:00:00+00:00')))
            ->assertJsonCount(2, 'data.products')
            ->json('data.id');

        $this->assertFalse($this->weekend->fresh()->featured);

        // PUT is a full replacement: absent flags and colours are cleared, the product list replaced.
        $this->putJson("/api/v1/flash-deals/{$id}", [
            'title' => 'API Deal renamed', 'start_date' => '2026-10-01', 'end_date' => '2026-10-02',
            'products' => [['id' => $b->id, 'discount' => 1.5, 'discount_type' => 'amount']],
        ])
            ->assertOk()
            ->assertJsonPath('data.slug', 'api-deal')
            ->assertJsonPath('data.status', false)
            ->assertJsonPath('data.featured', false)
            ->assertJsonPath('data.text_color', null)
            ->assertJsonPath('data.products', [['id' => $b->id, 'name' => $b->name, 'unit_price' => $b->unit_price, 'discount' => '1.50', 'discount_type' => 'amount']]);

        $this->deleteJson("/api/v1/flash-deals/{$id}")->assertNoContent();
        $this->assertNull(FlashDeal::find($id));
        $this->assertSame(0, DB::table('flash_deal_products')->where('flash_deal_id', $id)->count());
    }

    public function test_validation(): void
    {
        $p = Product::doesntHave('flashDeals')->where('unit_price', '>', 0)->orderBy('unit_price')->firstOrFail();
        $base = ['title' => 'X', 'start_date' => '2026-10-01', 'end_date' => '2026-10-02'];
        $row = ['id' => $p->id, 'discount' => 1, 'discount_type' => 'amount'];

        // Unreadable rows fail as 422, never 500 or a quietly different product.
        foreach ([[['id' => [$p->id]] + $row], [['id' => true] + $row], [['id' => 1.7] + $row], [['product_id' => $p->id, 'discount' => 1, 'discount_type' => 'amount']], [$p->id]] as $rows) {
            $this->postJson('/api/v1/flash-deals', $base + ['products' => $rows])
                ->assertUnprocessable()
                ->assertJsonPath('errors.products.0', 'Each product needs its numeric id.');
        }
        $this->postJson('/api/v1/flash-deals', $base + ['products' => [$row, ['discount' => 50] + $row]])
            ->assertUnprocessable()
            ->assertJsonPath('errors.products.0', 'Each product can appear only once in a deal.');

        $this->postJson('/api/v1/flash-deals', ['start_date' => ['x']] + $base + ['products' => [$row]])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('start_date');

        $this->postJson('/api/v1/flash-deals', $base + ['products' => [['discount_type' => 'half'] + $row]])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(["products.{$p->id}.discount_type" => 'Choose Percent or Amount for each included product.']);

        $taken = $this->weekend->products()->firstOrFail();
        $this->postJson('/api/v1/flash-deals', $base + ['products' => [['id' => $taken->id] + $row]])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('products');

        $this->postJson('/api/v1/flash-deals', $base + ['products' => [['id' => $p->id, 'discount' => (float) $p->unit_price + 1, 'discount_type' => 'amount']]])
            ->assertUnprocessable()
            ->assertJsonValidationErrors("products.{$p->id}.discount");

        $this->postJson('/api/v1/flash-deals', $base + ['products' => [['id' => 999999, 'discount' => 1, 'discount_type' => 'amount']]])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('products');

        $this->postJson('/api/v1/flash-deals', $base + ['products' => []])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('products');
    }

    public function test_flags_set_exactly_the_given_values(): void
    {
        $other = new FlashDeal(['title' => 'Other', 'start_date' => now(), 'end_date' => now()->addDay()]);
        $other->forceFill(['slug' => 'other'])->save();

        $this->patchJson("/api/v1/flash-deals/{$other->id}/flags", ['featured' => true])
            ->assertOk()
            ->assertJsonPath('data.featured', true)
            ->assertJsonPath('data.status', false);
        $this->assertFalse($this->weekend->fresh()->featured);

        // Idempotent: repeating it changes nothing.
        $this->patchJson("/api/v1/flash-deals/{$other->id}/flags", ['featured' => true, 'status' => true])
            ->assertOk()
            ->assertJsonPath('data.featured', true)
            ->assertJsonPath('data.status', true);

        $this->patchJson("/api/v1/flash-deals/{$other->id}/flags", [])->assertUnprocessable();
        $this->patchJson("/api/v1/flash-deals/{$other->id}/flags", ['status' => 'maybe'])->assertUnprocessable()->assertJsonValidationErrors('status');
    }
}
