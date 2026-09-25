<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\FlashDeal;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\Upload;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::where('email', 'admin@wowmart.test')->firstOrFail();
    }

    /** The smallest valid form post; tests override what they care about. */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Ultrasonic Distance Sensor HC-SR04',
            'category_id' => Category::first()->id,
            'unit' => 'pc',
            'min_qty' => 1,
            'unit_price' => 150,
            'discount' => 0,
            'discount_type' => 'amount',
            'current_stock' => 40,
            'low_stock_quantity' => 5,
            'stock_visibility_state' => 'quantity',
            'shipping_type' => 'free',
        ], $overrides);
    }

    private function images(int $n): array
    {
        return Upload::where('type', 'image')->orderBy('id')->limit($n)->pluck('id')->all();
    }

    // -- list ---------------------------------------------------------------

    public function test_the_list_filters_by_stock_the_way_the_dashboard_links_to_it(): void
    {
        $soldOut = Product::where('current_stock', '<=', 0)->sole();

        $this->actingAs($this->admin)->get('/admin/products/all?stock=out')
            ->assertOk()
            ->assertSee($soldOut->name)
            ->assertSee('Showing 1–1 of 1 product');
    }

    public function test_the_list_searches_by_sku(): void
    {
        $this->actingAs($this->admin)->get('/admin/products/all?search=WM-00001')
            ->assertOk()
            ->assertSee(Product::where('sku', 'WM-00001')->value('name'))
            ->assertSee('of 1 product');
    }

    public function test_unknown_filter_values_are_ignored_not_rejected(): void
    {
        $this->actingAs($this->admin)->get('/admin/products/all?stock=bogus&sort=DROP+TABLE')
            ->assertOk()
            ->assertSee('of 42 products');
    }

    // -- create -------------------------------------------------------------

    public function test_an_admin_creates_an_in_house_product_with_its_relations(): void
    {
        [$a, $b, $c] = $this->images(3);
        $extra = Category::skip(1)->first();

        $this->actingAs($this->admin)
            ->post('/admin/products/store', $this->payload([
                'category_ids' => [$extra->id],
                'photos' => [$c, $a, $b],
                'featured' => '1',
            ]))
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $product = Product::latest('id')->first();

        $this->assertSame('ultrasonic-distance-sensor-hc-sr04', $product->slug);
        $this->assertSame('admin', $product->added_by);
        $this->assertSame($this->admin->id, $product->user_id);
        $this->assertTrue($product->featured);
        $this->assertFalse($product->todays_deal, 'an unticked box must save as false');
        // The main category is listed even though only the extra one was ticked.
        $this->assertEqualsCanonicalizing([Category::first()->id, $extra->id], $product->categories->pluck('id')->all());
        // Gallery keeps the order it was chosen in; no thumbnail picked, so the first image is used.
        $this->assertSame([$c, $a, $b], $product->gallery->pluck('id')->all());
        $this->assertSame($c, $product->thumbnail_img);
        // Exactly one stock row mirrors the product's stock.
        $this->assertSame(40, $product->stocks()->sole()->qty);
    }

    public function test_ownership_and_lifecycle_fields_cannot_be_posted(): void
    {
        $seller = User::factory()->seller()->create();

        $this->actingAs($this->admin)->post('/admin/products/store', $this->payload([
            'slug' => 'chosen-slug',
            'added_by' => 'seller',
            'user_id' => $seller->id,
            'approved' => '0',
            'num_of_sale' => 999,
            'rating' => 5,
        ]))->assertSessionHasNoErrors();

        $product = Product::latest('id')->first();

        $this->assertNotSame('chosen-slug', $product->slug);
        $this->assertSame('admin', $product->added_by);
        $this->assertSame($this->admin->id, $product->user_id);
        $this->assertTrue($product->approved);
        $this->assertSame(0, $product->num_of_sale);
    }

    public function test_a_repeated_name_gets_a_unique_slug(): void
    {
        $existing = Product::first();

        $this->actingAs($this->admin)->post('/admin/products/store', $this->payload(['name' => $existing->name]));

        $this->assertSame($existing->slug.'-2', Product::latest('id')->value('slug'));
    }

    /** @return array<string, array{array, string}> */
    public static function invalidInput(): array
    {
        return [
            'percentage over 100' => [['discount' => 120, 'discount_type' => 'percent'], 'discount'],
            'flat discount over the price' => [['discount' => 151], 'discount'],
            'discount ends before it starts' => [['discount_start_date' => '2026-10-10T10:00', 'discount_end_date' => '2026-10-01T10:00'], 'discount_end_date'],
            'flat rate without a cost' => [['shipping_type' => 'flat_rate'], 'shipping_cost'],
            'warranty without a period' => [['has_warranty' => '1'], 'warranty_id'],
            'unknown category' => [['category_id' => 99999], 'category_id'],
            'negative stock' => [['current_stock' => -1], 'current_stock'],
            'video link that is not https' => [['video_provider' => 'youtube', 'video_link' => 'javascript:alert(1)'], 'video_link'],
            'flash deal without a discount' => [['flash_deal_id' => 1], 'flash_discount'],
        ];
    }

    #[DataProvider('invalidInput')]
    public function test_invalid_input_is_refused(array $overrides, string $field): void
    {
        $before = Product::count();

        $this->actingAs($this->admin)
            ->from('/admin/products/create')
            ->post('/admin/products/store', $this->payload($overrides))
            ->assertRedirect('/admin/products/create')
            ->assertSessionHasErrors($field);

        $this->assertSame($before, Product::count());
    }

    public function test_a_failed_save_shows_what_was_typed_including_unticked_boxes(): void
    {
        $product = Product::where('featured', true)->firstOrFail();

        // Untick "featured", break the price: the page must come back unticked.
        $this->actingAs($this->admin)
            ->from("/admin/products/{$product->id}/edit")
            ->put("/admin/products/{$product->id}", $this->payload(['name' => 'Typed name', 'unit_price' => -5]))
            ->assertSessionHasErrors('unit_price');

        $html = $this->actingAs($this->admin)->get("/admin/products/{$product->id}/edit")->getContent();

        $this->assertStringContainsString('value="Typed name"', $html);
        $this->assertDoesNotMatchRegularExpression('/name="featured" value="1"\s+checked/', $html);
    }

    // -- update -------------------------------------------------------------

    public function test_updating_keeps_the_slug_and_can_empty_the_gallery(): void
    {
        $product = Product::has('gallery')->firstOrFail();
        $slug = $product->slug;

        $this->actingAs($this->admin)
            ->put("/admin/products/{$product->id}", $this->payload(['name' => 'Renamed product', 'current_stock' => 7]))
            ->assertSessionHasNoErrors();

        $product->refresh();
        $this->assertSame('Renamed product', $product->name);
        $this->assertSame($slug, $product->slug, 'renaming must not break existing links');
        $this->assertCount(0, $product->gallery, 'no photos[] posted means every image was unticked');
        $this->assertSame(7, $product->stocks()->sole()->qty);
    }

    public function test_a_product_can_be_put_in_a_flash_deal_and_taken_out(): void
    {
        $product = Product::doesntHave('flashDeals')->firstOrFail();
        $deal = FlashDeal::firstOrFail();

        $this->actingAs($this->admin)->put("/admin/products/{$product->id}", $this->payload([
            'flash_deal_id' => $deal->id, 'flash_discount' => 12, 'flash_discount_type' => 'percent',
        ]))->assertSessionHasNoErrors();

        $this->assertSame('12.00', number_format($product->flashDeals()->sole()->pivot->discount, 2));

        $this->actingAs($this->admin)->put("/admin/products/{$product->id}", $this->payload());
        $this->assertCount(0, $product->flashDeals()->get());
    }

    // -- delete, duplicate, toggle -------------------------------------------

    public function test_a_product_that_has_been_ordered_cannot_be_deleted(): void
    {
        $ordered = Product::findOrFail(OrderDetail::value('product_id'));

        $this->actingAs($this->admin)
            ->from('/admin/products/all')
            ->delete("/admin/products/{$ordered->id}")
            ->assertRedirect('/admin/products/all')
            ->assertSessionHas('error', fn ($m) => str_contains($m, 'Unpublish it instead'));

        $this->assertModelExists($ordered);
    }

    public function test_a_never_ordered_product_is_deleted_with_its_rows(): void
    {
        $product = Product::whereNotIn('id', OrderDetail::select('product_id'))->firstOrFail();
        $stockIds = $product->stocks()->pluck('id');

        $this->actingAs($this->admin)
            ->delete("/admin/products/{$product->id}")
            ->assertRedirect('/admin/products/all');

        $this->assertModelMissing($product);
        $this->assertDatabaseMissing('product_stocks', ['id' => $stockIds->first()]);
        $this->assertDatabaseMissing('product_categories', ['product_id' => $product->id]);
    }

    public function test_duplicating_makes_an_unpublished_copy_with_fresh_sales(): void
    {
        $source = Product::where('num_of_sale', '>', 0)->has('gallery')->firstOrFail();

        $this->actingAs($this->admin)->post("/admin/products/{$source->id}/duplicate")->assertRedirect();

        $copy = Product::latest('id')->first();

        $this->assertSame($source->name, $copy->name);
        $this->assertNotSame($source->slug, $copy->slug);
        $this->assertFalse($copy->published);
        $this->assertSame(0, $copy->num_of_sale);
        $this->assertSame($source->gallery->pluck('id')->all(), $copy->gallery->pluck('id')->all());
        $this->assertSame($source->stocks()->sole()->qty, $copy->stocks()->sole()->qty);
    }

    public function test_a_switch_flips_one_field(): void
    {
        $product = Product::where('published', true)->firstOrFail();

        $this->actingAs($this->admin)->post("/admin/products/{$product->id}/toggle/published")->assertRedirect();

        $this->assertFalse($product->fresh()->published);
    }

    public function test_only_whitelisted_fields_can_be_switched(): void
    {
        $product = Product::firstOrFail();

        $this->actingAs($this->admin)->post("/admin/products/{$product->id}/toggle/user_id")->assertNotFound();
    }
}
