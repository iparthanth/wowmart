<?php

namespace Tests\Feature\Admin;

use App\Models\FlashDeal;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FlashDealTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private FlashDeal $weekend;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::where('email', 'admin@wowmart.test')->firstOrFail();
        $this->weekend = FlashDeal::where('slug', 'weekend-maker-sale')->firstOrFail();
    }

    /** The form's shape: every product posted, only ticked rows included. */
    private function form(array $rows, array $overrides = []): array
    {
        return $overrides + [
            'title' => 'Solder Week',
            'start_date' => '2026-10-01T09:00',
            'end_date' => '2026-10-08T09:00',
            'background_color' => '#112233',
            'text_color' => '#FFFFFF',
            'status' => '1',
            'products' => $rows,
        ];
    }

    /** The cheapest product not already in a deal (a product can be in one deal at a time). */
    private function cheapest(): Product
    {
        return Product::doesntHave('flashDeals')->where('unit_price', '>', 0)->orderBy('unit_price')->firstOrFail();
    }

    public function test_the_list_shows_state_count_and_page_link(): void
    {
        $this->actingAs($this->admin)->get('/admin/flash_deals')
            ->assertOk()
            ->assertSeeInOrder(['Weekend Maker Sale', 'Running'])
            ->assertSee(config('services.storefront.url').'/flash-deal/weekend-maker-sale', false)
            ->assertViewHas('deals', fn ($page) => $page->first()->products_count === $this->weekend->products()->count());
    }

    public function test_state_follows_the_switch_and_the_dates(): void
    {
        $d = new FlashDeal(['start_date' => now()->addDay(), 'end_date' => now()->addDays(2)]);
        $d->status = true;
        $this->assertSame('scheduled', $d->state());

        $d->start_date = now()->subDays(2);
        $d->end_date = now()->subDay();
        $this->assertSame('ended', $d->state());

        $d->end_date = now()->addDay();
        $this->assertSame('running', $d->state());

        $d->status = false;
        $this->assertSame('off', $d->state());
    }

    public function test_create_and_edit_forms_render_with_the_product_table(): void
    {
        $this->actingAs($this->admin)->get('/admin/flash_deals/create')
            ->assertOk()
            ->assertSee('name="products['.$this->cheapest()->id.'][include]"', false);

        $included = $this->weekend->products()->first();
        $this->actingAs($this->admin)->get("/admin/flash_deals/{$this->weekend->id}/edit")
            ->assertOk()
            ->assertSee('id="inc-'.$included->id.'" name="products['.$included->id.'][include]" value="1" checked', false);
    }

    public function test_create_stores_only_ticked_products_with_their_pivot_values(): void
    {
        [$a, $b, $c] = Product::doesntHave('flashDeals')->orderBy('id')->limit(3)->get();

        $this->actingAs($this->admin)
            ->post('/admin/flash_deals', $this->form([
                $a->id => ['include' => '1', 'discount' => '15', 'discount_type' => 'percent'],
                $b->id => ['include' => '1', 'discount' => '0', 'discount_type' => 'amount'],
                $c->id => ['discount' => '50', 'discount_type' => 'percent'], // not ticked
            ]))
            ->assertSessionHasNoErrors()
            ->assertRedirect('/admin/flash_deals');

        $deal = FlashDeal::where('title', 'Solder Week')->firstOrFail();
        $this->assertSame('solder-week', $deal->slug);
        $this->assertTrue($deal->status);
        $this->assertFalse($deal->featured);
        $this->assertSame('#112233', $deal->background_color);

        $pivot = $deal->products()->get()->mapWithKeys(fn ($p) => [$p->id => [(float) $p->pivot->discount, $p->pivot->discount_type]])->all();
        $this->assertEquals([$a->id => [15.0, 'percent'], $b->id => [0.0, 'amount']], $pivot);
    }

    public function test_a_flat_discount_above_the_price_is_refused(): void
    {
        $p = $this->cheapest();

        $this->actingAs($this->admin)
            ->post('/admin/flash_deals', $this->form([$p->id => ['include' => '1', 'discount' => (string) ($p->unit_price + 1), 'discount_type' => 'amount']]))
            ->assertSessionHasErrors("products.{$p->id}.discount");

        $this->actingAs($this->admin)
            ->post('/admin/flash_deals', $this->form([$p->id => ['include' => '1', 'discount' => (string) $p->unit_price, 'discount_type' => 'amount']]))
            ->assertSessionHasNoErrors();
    }

    public function test_a_percentage_cannot_exceed_100_and_discounts_cannot_be_negative(): void
    {
        $p = $this->cheapest();

        $this->actingAs($this->admin)
            ->post('/admin/flash_deals', $this->form([$p->id => ['include' => '1', 'discount' => '101', 'discount_type' => 'percent']]))
            ->assertSessionHasErrors(["products.{$p->id}.discount" => 'A percentage discount cannot be more than 100.']);

        $this->actingAs($this->admin)
            ->post('/admin/flash_deals', $this->form([$p->id => ['include' => '1', 'discount' => '-1', 'discount_type' => 'percent']]))
            ->assertSessionHasErrors("products.{$p->id}.discount");

        $this->assertSame(1, FlashDeal::count());
    }

    public function test_dates_must_be_in_order_and_at_least_one_product_included(): void
    {
        $p = $this->cheapest();

        $this->actingAs($this->admin)
            ->post('/admin/flash_deals', $this->form(
                [$p->id => ['include' => '1', 'discount' => '5', 'discount_type' => 'percent']],
                ['start_date' => '2026-10-08T09:00', 'end_date' => '2026-10-01T09:00'],
            ))
            ->assertSessionHasErrors(['end_date' => 'The deal must end after it starts.']);

        $this->actingAs($this->admin)
            ->post('/admin/flash_deals', $this->form([$p->id => ['discount' => '5', 'discount_type' => 'percent']]))
            ->assertSessionHasErrors(['products' => 'Include at least one product in the deal.']);
    }

    public function test_colours_must_be_hex(): void
    {
        $p = $this->cheapest();

        $this->actingAs($this->admin)
            ->post('/admin/flash_deals', $this->form([$p->id => ['include' => '1', 'discount' => '5', 'discount_type' => 'percent']], ['text_color' => 'red']))
            ->assertSessionHasErrors('text_color');
    }

    public function test_featuring_a_deal_on_save_unfeatures_the_others(): void
    {
        $p = $this->cheapest();
        $this->assertTrue($this->weekend->featured);

        $this->actingAs($this->admin)
            ->post('/admin/flash_deals', $this->form([$p->id => ['include' => '1', 'discount' => '5', 'discount_type' => 'percent']], ['featured' => '1']))
            ->assertSessionHasNoErrors();

        $this->assertFalse($this->weekend->fresh()->featured);
        $this->assertSame(['Solder Week'], FlashDeal::where('featured', true)->pluck('title')->all());
    }

    public function test_featuring_a_deal_by_toggle_unfeatures_the_others(): void
    {
        $other = new FlashDeal(['title' => 'Other', 'start_date' => now(), 'end_date' => now()->addDay()]);
        $other->forceFill(['slug' => 'other'])->save();

        $this->actingAs($this->admin)->from('/admin/flash_deals')
            ->post("/admin/flash_deals/{$other->id}/toggle/featured")
            ->assertRedirect('/admin/flash_deals')
            ->assertSessionHas('status');

        $this->assertTrue($other->fresh()->featured);
        $this->assertFalse($this->weekend->fresh()->featured);

        $this->actingAs($this->admin)->post("/admin/flash_deals/{$other->id}/toggle/status");
        $this->assertTrue($other->fresh()->status);

        $this->actingAs($this->admin)->post("/admin/flash_deals/{$other->id}/toggle/title")->assertNotFound();
    }

    public function test_renaming_keeps_the_slug_and_replaces_the_products(): void
    {
        $p = $this->cheapest();

        $this->actingAs($this->admin)
            ->put("/admin/flash_deals/{$this->weekend->id}", $this->form(
                [$p->id => ['include' => '1', 'discount' => '0.5', 'discount_type' => 'amount']],
                ['title' => 'Maker Month', 'featured' => '1'],
            ))
            ->assertSessionHasNoErrors()
            ->assertRedirect("/admin/flash_deals/{$this->weekend->id}/edit");

        $deal = $this->weekend->fresh();
        $this->assertSame('Maker Month', $deal->title);
        $this->assertSame('weekend-maker-sale', $deal->slug);
        $this->assertSame([$p->id], $deal->products()->pluck('products.id')->all());
    }

    public function test_a_new_deal_with_a_taken_title_gets_a_distinct_slug(): void
    {
        $p = $this->cheapest();

        $this->actingAs($this->admin)
            ->post('/admin/flash_deals', $this->form([$p->id => ['include' => '1', 'discount' => '5', 'discount_type' => 'percent']], ['title' => 'Weekend Maker Sale']))
            ->assertSessionHasNoErrors();

        $this->assertSame(['weekend-maker-sale', 'weekend-maker-sale-2'], FlashDeal::orderBy('id')->pluck('slug')->all());
    }

    public function test_delete_detaches_the_products_and_keeps_them(): void
    {
        $ids = $this->weekend->products()->pluck('products.id')->all();
        $this->assertNotEmpty($ids);

        $this->actingAs($this->admin)
            ->delete("/admin/flash_deals/{$this->weekend->id}")
            ->assertRedirect('/admin/flash_deals')
            ->assertSessionHas('status', fn ($s) => str_contains($s, 'normal prices'));

        $this->assertModelMissing($this->weekend);
        $thi