<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Services\CategoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::where('email', 'admin@wowmart.test')->firstOrFail();
    }

    private function make(string $name, ?Category $parent = null): Category
    {
        return app(CategoryService::class)->create(['name' => $name, 'parent_id' => $parent?->id, 'order_level' => 0]);
    }

    /** An empty category, so delete guards only see what each test adds. */
    private function empty(): Category
    {
        return $this->make('Empty shelf');
    }

    public function test_the_list_shows_the_tree_with_product_counts_and_filters_by_name(): void
    {
        $parent = Category::whereHas('products')->firstOrFail();
        $child = $this->make('Zeta sub-shelf', $parent);
        $count = $parent->products()->count();

        $this->actingAs($this->admin)->get('/admin/categories')
            ->assertOk()
            ->assertSeeInOrder([$parent->name, $child->name])
            ->assertSee('Add new category');

        $this->assertSame($count, $this->get('/admin/categories')->viewData('productCounts')[$parent->id]);

        $this->get('/admin/categories?search=Zeta')
            ->assertOk()
            ->assertSee($child->name)
            ->assertDontSee('>'.$parent->name.'</a>', false);

        // % and _ are literal, and a malformed ?search[] is ignored rather than a server error.
        $sale = $this->make('Half 50% off');
        $this->assertSame([$sale->id], $this->get('/admin/categories?search=%25')->viewData('categories')->pluck('id')->all());
        $this->assertCount(0, $this->get('/admin/categories?search=_')->viewData('categories'));
        $this->get('/admin/categories?search[]=x')->assertOk();
    }

    public function test_creating_top_level_and_child_categories_sets_levels_and_slug(): void
    {
        $this->actingAs($this->admin)
            ->post('/admin/categories', ['name' => 'Robot Kits', 'order_level' => 3, 'featured' => '1'])
            ->assertRedirect('/admin/categories')
            ->assertSessionHas('status');

        $top = Category::where('name', 'Robot Kits')->sole();
        $this->assertSame([0, 'robot-kits', 3, true, false], [$top->level, $top->slug, $top->order_level, $top->featured, $top->digital]);

        $this->post('/admin/categories', ['name' => 'Wheeled Robots', 'parent_id' => $top->id])->assertSessionHasNoErrors();
        $child = Category::where('name', 'Wheeled Robots')->sole();
        $this->assertSame([1, $top->id, 0], [$child->level, $child->parent_id, $child->order_level]);
    }

    public function test_renaming_keeps_the_slug(): void
    {
        $c = $this->make('Old name');

        $this->actingAs($this->admin)->put("/admin/categories/{$c->id}", ['name' => 'New name'])
            ->assertRedirect("/admin/categories/{$c->id}/edit");

        $this->assertSame(['New name', 'old-name'], [$c->fresh()->name, $c->fresh()->slug]);
    }

    public function test_moving_a_category_relevels_its_whole_branch(): void
    {
        $a = $this->make('A');
        $b = $this->make('B', $a);
        $c = $this->make('C', $b);
        $d = $this->make('D', $c);
        $x = $this->make('X');
        $y = $this->make('Y', $x);
        $levels = fn () => [$b->fresh()->level, $c->fresh()->level, $d->fresh()->level];

        // Move B (with C and D under it) under Y: two generations below B must follow.
        $this->actingAs($this->admin)->put("/admin/categories/{$b->id}", ['name' => 'B', 'parent_id' => $y->id])
            ->assertSessionHasNoErrors();
        $this->assertSame([2, 3, 4], $levels());

        // And back to the top.
        $this->put("/admin/categories/{$b->id}", ['name' => 'B'])->assertSessionHasNoErrors();
        $this->assertSame([0, 1, 2], $levels());
    }

    public function test_a_category_cannot_become_its_own_ancestor(): void
    {
        $a = $this->make('A');
        $b = $this->make('B', $a);
        $c = $this->make('C', $b);

        $this->actingAs($this->admin);
        foreach ([$a, $b, $c] as $target) {
            $this->from("/admin/categories/{$a->id}/edit")
                ->put("/admin/categories/{$a->id}", ['name' => 'A', 'parent_id' => $target->id])
                ->assertSessionHasErrors('parent_id');
        }
        $this->assertNull($a->fresh()->parent_id);

        // The edit form does not even offer them.
        $parents = $this->get("/admin/categories/{$a->id}/edit")->assertOk()->viewData('parents')->pluck('id');
        $this->assertEmpty($parents->intersect([$a->id, $b->id, $c->id]));
    }

    public function test_descendant_ids_terminates_on_a_cycle_already_in_the_data(): void
    {
        $a = $this->make('A');
        $b = $this->make('B', $a);
        $a->forceFill(['parent_id' => $b->id])->saveQuietly();

        $this->assertEqualsCanonicalizing([$b->id], $a->descendantIds());
    }

    public function test_delete_is_refused_while_subcategories_exist(): void
    {
        $parent = $this->empty();
        $this->make('Child one', $parent);
        $this->make('Child two', $parent);

        $this->actingAs($this->admin)->from('/admin/categories')
            ->delete("/admin/categories/{$parent->id}")
            ->assertSessionHas('error', '"Empty shelf" cannot be deleted yet. Move or delete its 2 subcategories first.');

        $this->assertModelExists($parent);
    }

    public function test_delete_is_refused_while_products_use_it(): void
    {
        // Only listed (pivot), only main category and only frequently-bought category are all caught.
        $listed = $this->empty();
        Product::first()->categories()->attach($listed);
        $main = $this->make('Main only');
        Product::skip(1)->first()->forceFill(['category_id' => $main->id])->save();
        $fq = $this->make('Bought together');
        Product::skip(2)->first()->forceFill(['frequently_bought_selection_type' => 'category', 'fq_bought_product_category_id' => $fq->id])->save();

        $this->actingAs($this->admin)->from('/admin/categories');
        $this->delete("/admin/categories/{$listed->id}")->assertSessionHas('error', '"Empty shelf" is used by 1 product. Move it to another category first.');
        $this->delete("/admin/categories/{$main->id}")->assertSessionHas('error');
        $this->delete("/admin/categories/{$fq->id}")->assertSessionHas('error');

        $this->assertModelExists($listed);
        $this->assertModelExists($main);
        $this->assertModelExists($fq);
    }

    public function test_an_unused_category_can_be_deleted(): void
    {
        $c = $this->empty();

        $this->actingAs($this->admin)->delete("/admin/categories/{$c->id}")
            ->assertRedirect('/admin/categories')
            ->assertSessionHas('status');

        $this->assertModelMissing($c);
    }

    public function test_the_featured_switch_toggles(): void
    {
        $c = $this->empty();

        $this->actingAs($this->admin)->from('/admin/categories')
            ->post("/admin/categories/{$c->id}/toggle/featured")->assertRedirect('/admin/categories');
        $this->assertTrue($c->fresh()->featured);

        $this->post("/admin/categories/{$c->id}/toggle/featured");
        $this->assertFalse($c->fresh()->featured);

        $this->post("/admin/categories/{$c->id}/toggle/digital")->assertNotFound();
    }

    public function test_validation_explains_what_to_fix(): void
    {
        $this->actingAs($this->admin)->from('/admin/categories/create')
            ->post('/admin/categories', ['name' => '', 'order_level' => 10000, 'parent_id' => 999999])
            ->assertRedirect('/admin/categories/create')
            ->assertSessionHasErrors(['name', 'order_level', 'parent_id']);

        $this->get('/admin/categories/create')->assertOk();
    }
}
