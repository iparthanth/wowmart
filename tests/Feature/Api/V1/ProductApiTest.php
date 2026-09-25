<?php

namespace Tests\Feature\Api\V1;

use App\Models\Brand;
use App\Models\Category;
use App\Models\FlashDeal;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\Upload;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProductApiTest extends TestCase
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

    /** The smallest valid body; tests override what they care about. */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Ultrasonic Distance Sensor HC-SR04',
            'category_id' => Category::first()->id,
            'min_qty' => 1,
            'unit_price' => 150,
            'discount_type' => 'amount',
            'current_stock' => 40,
            'low_stock_quantity' => 5,
            'stock_visibility_state' => 'quantity',
            'shipping_type' => 'free',
        ], $overrides);
    }

    public function test_the_list_is_paginated_and_uses_the_panel_filters(): void
    {
        $this->getJson('/api/v1/products?per_page=5')
            ->assertOk()
            ->assertJsonCount(5, 'data')
            ->assertJsonPath('meta.total', 42)
            ->assertJsonStructure(['data' => [['id', 'slug', 'owner' => ['id', 'name'], 'thumbnail', 'gallery', 'category_ids', 'flash_deal', 'stock']], 'links', 'meta']);

        $soldOut = Product::where('current_stock', '<=', 0)->sole();
        $this->getJson('/api/v1/products?stock=out')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.id', $soldOut->id)
            ->assertJsonPath('data.0.stock', 0);

        $this->getJson('/api/v1/products?search=WM-00001')->assertJsonPath('meta.total', 1);
        $this->getJson('/api/v1/products?added_by=seller')->assertJsonPath('meta.total', Product::fromSellers()->count());

        $this->getJson('/api/v1/products?sort=price_asc&per_page=1')
            ->assertJsonPath('data.0.id', Product::orderBy('unit_price')->orderByDesc('id')->value('id'));

        // Unknown values are ignored, not rejected.
        $this->getJson('/api/v1/products?stock=bogus&sort=DROP+TABLE&per_page=500')
            ->assertOk()
            ->assertJsonPath('meta.total', 42)
            ->assertJsonPath('meta.per_page', 100);
    }

    public function test_the_list_does_not_query_per_product(): void
    {
        $queries = 0;
        DB::listen(function () use (&$queries) {
            $queries++;
        });

        $this->getJson('/api/v1/products?per_page=40')->assertOk();

        // count + page + one per eager-loaded relation, whatever the page size
        $this->assertLessThanOrEqual(12, $queries);
    }

    public function test_show_serialises_types_the_contract_way(): void
    {
        $product = Product::has('gallery')->firstOrFail();

        $this->getJson("/api/v1/products/{$product->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $product->id)
            ->assertJsonPath('data.unit_price', number_format($product->unit_price, 2, '.', ''))
            ->assertJsonPath('data.published', true)
            ->assertJsonPath('data.discount_type', 'amount')
            ->assertJsonPath('data.owner.id', $product->user_id)
            ->assertJsonPath('data.gallery.0.id', $product->gallery->first()->id)
            ->assertJsonPath('data.created_at', $product->created_at->toIso8601String());
    }

    public function test_create_is_201_with_relations_and_ignores_system_fields(): void
    {
        $seller = User::factory()->seller()->create();
        [$a, $b] = Upload::where('type', 'image')->orderBy('id')->limit(2)->pluck('id')->all();
        $extra = Category::skip(1)->firstOrFail();
        $deal = FlashDeal::firstOrFail();

        $this->postJson('/api/v1/products', $this->payload([
            'category_ids' => [$extra->id],
            'photos' => [$b, $a],
            'featured' => true,
            'flash_deal_id' => $deal->id, 'flash_discount' => 12, 'flash_discount_type' => 'percent',
            // none of these may come from input
            'slug' => 'chosen-slug', 'added_by' => 'seller', 'user_id' => $seller->id, 'num_of_sale' => 999,
        ]))
            ->assertCreated()
            ->assertJsonPath('data.slug', 'ultrasonic-distance-sensor-hc-sr04')
            ->assertJsonPath('data.added_by', 'admin')
            ->assertJsonPath('data.owner.id', $this->admin->id)
            ->assertJsonPath('data.num_of_sale', 0)
            ->assertJsonPath('data.approved', true)
            ->assertJsonPath('data.featured', true)
            ->assertJsonPath('data.todays_deal', false)
            ->assertJsonPath('data.category_ids', collect([Category::first()->id, $extra->id])->sort()->values()->all())
            ->assertJsonPath('data.gallery.*.id', [$b, $a])
            ->assertJsonPath('data.thumbnail.id', $b)
            ->assertJsonPath('data.flash_deal', ['id' => $deal->id, 'discount' => '12.00', 'discount_type' => 'percent'])
            ->assertJsonPath('data.stock', 40);
    }

    public function test_put_is_a_full_replacement(): void
    {
        $product = Product::has('gallery')->firstOrFail();
        $slug = $product->slug;

        // No photos / category_ids / flash_deal_id sent: those lists become empty, as on the form.
        $this->putJson("/api/v1/products/{$product->id}", $this->payload(['name' => 'Renamed product', 'current_stock' => 7]))
            ->assertOk()
            ->assertJsonPath('data.name', 'Renamed product')
            ->assertJsonPath('data.slug', $slug)
            ->assertJsonPath('data.gallery', [])
            ->assertJsonPath('data.category_ids', [Category::first()->id])
            ->assertJsonPath('data.stock', 7);
    }

    public function test_put_resets_every_omitted_field_so_no_stale_discount_survives(): void
    {
        $product = Product::firstOrFail();
        $product->forceFill([
            'unit_price' => 500, 'discount' => 150, 'discount_type' => 'amount', 'brand_id' => Brand::first()->id,
            'description' => 'Old description', 'sku' => 'OLD-SKU', 'meta_title' => 'Old meta', 'weight' => 2,
        ])->save();

        // The old flat 150 would become a 150% discount at the new price if it were kept.
        $this->putJson("/api/v1/products/{$product->id}", $this->payload(['unit_price' => 10, 'discount_type' => 'percent']))
            ->assertOk()
            ->assertJsonPath('data.discount', '0.00')
            ->assertJsonPath('data.brand_id', null)
            ->assertJsonPath('data.description', null)
            ->assertJsonPath('data.sku', null)
            ->assertJsonPath('data.meta_title', null)
            ->assertJsonPath('data.weight', '0.000');

        $this->assertSame(10.0, $product->refresh()->discountedPrice());

        // An explicit null on a zero-default column means "none", not a database error.
        $this->putJson("/api/v1/products/{$product->id}", $this->payload(['discount' => null, 'weight' => null]))
            ->assertOk()
            ->assertJsonPath('data.discount', '0.00');
    }

    public function test_a_get_body_can_be_put_back_unchanged(): void
    {
        $product = Product::has('gallery')->firstOrFail();
        $product->flashDeals()->sync([FlashDeal::firstOrFail()->id => ['discount' => 12, 'discount_type' => 'percent']]);

        $before = $this->getJson("/api/v1/products/{$product->id}")->assertOk()->json('data');
        $this->assertNotEmpty($before['photos']);
        $this->assertSame('12.00', $before['flash_discount']);

        $after = $this->putJson("/api/v1/products/{$product->id}", $before)->assertOk()->json('data');

        unset($before['updated_at'], $after['updated_at']);
        $this->assertSame($before, $after);
    }

    public function test_bad_input_is_422(): void
    {
        $this->postJson('/api/v1/products', $this->payload(['name' => '', 'unit_price' => 10, 'discount' => 50, 'shipping_type' => 'flat_rate']))
            ->assertUnprocessable()
            ->assertJsonStructure(['message', 'errors'])
            ->assertJsonValidationErrors(['name', 'discount', 'shipping_cost'])
            ->assertJsonPath('errors.discount.0', 'A flat discount cannot be more than the price.');
    }

    public function test_delete_is_204_or_409_for_an_ordered_product(): void
    {
        $ordered = Product::findOrFail(OrderDetail::value('product_id'));
        $this->deleteJson("/api/v1/products/{$ordered->id}")
            ->assertStatus(409)
            ->assertJsonPath('message', fn (string $m) => str_contains($m, 'Unpublish it instead'));
        $this->assertModelExists($ordered);

        $free = Product::whereNotIn('id', OrderDetail::select('product_id'))->firstOrFail();
        $this->deleteJson("/api/v1/products/{$free->id}")->assertNoContent();
        $this->assertModelMissing($free);
    }

    public function test_duplicate_is_201_and_unpublished(): void
    {
        $source = Product::where('num_of_sale', '>', 0)->has('gallery')->firstOrFail();

        $this->postJson("/api/v1/products/{$source->id}/duplicate")
            ->assertCreated()
            ->assertJsonPath('data.name', $source->name)
            ->assertJsonPath('data.published', false)
            ->assertJsonPath('data.num_of_sale', 0)
            ->assertJsonPath('data.slug', fn (string $s) => $s !== $source->slug)
            ->assertJsonPath('data.gallery.*.id', $source->gallery->pluck('id')->all());
    }

    public function test_flags_set_exactly_the_given_values(): void
    {
        $product = Product::where('published', true)->where('featured', false)->firstOrFail();

        foreach ([false, false, true] as $on) {
            $this->patchJson("/api/v1/products/{$product->id}/flags", ['published' => $on, 'featured' => $on])
                ->assertOk()
                ->assertJsonPath('data.published', $on)
                ->assertJsonPath('data.featured', $on)
                ->assertJsonPath('data.approved', true);
        }

        $this->patchJson("/api/v1/products/{$product->id}/flags", [])->assertUnprocessable()->assertJsonValidationErrors('flags');
        $this->patchJson("/api/v1/products/{$product->id}/flags", ['approved' => 'maybe'])->assertJsonValidationErrors('approved');
        // Keys outside the four switches are ignored, so this body carries no flag at all.
        $this->patchJson("/api/v1/products/{$product->id}/flags", ['user_id' => 999])->assertUnprocessable();
    }

    public function test_requests_without_a_token_are_401(): void
    {
        $this->app['auth']->forgetGuards();

        $this->getJson('/api/v1/products')->assertUnauthorized();
        $this->postJson('/api/v1/products', $this->payload())->assertUnauthorized();
    }
}
