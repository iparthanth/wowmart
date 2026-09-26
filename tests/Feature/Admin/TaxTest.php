<?php

namespace Tests\Feature\Admin;

use App\Models\Product;
use App\Models\ProductTax;
use App\Models\Tax;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaxTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::where('email', 'admin@wowmart.test')->firstOrFail();
    }

    private function vat(): Tax
    {
        return Tax::where('name', 'VAT')->firstOrFail();
    }

    public function test_the_page_lists_both_seeded_taxes_and_the_assignment_form(): void
    {
        $this->actingAs($this->admin)->get('/admin/taxes')
            ->assertOk()
            ->assertSee('Add a tax')
            ->assertSee('Charge a product under a tax')
            ->assertSee('VAT')
            ->assertSee('GST')
            ->assertViewHas('taxes', fn ($page) => $page->total() === 2);
    }

    public function test_a_tax_is_created_edited_and_name_checked(): void
    {
        $this->actingAs($this->admin)->post('/admin/taxes', ['name' => 'Import Duty', 'status' => '1'])
            ->assertRedirect('/admin/taxes')->assertSessionHasNoErrors();
        $this->assertSame(3, Tax::count());

        $this->actingAs($this->admin)->post('/admin/taxes', ['name' => 'VAT', 'status' => '1'])
            ->assertSessionHasErrors('name');

        $duty = Tax::where('name', 'Import Duty')->firstOrFail();
        $this->actingAs($this->admin)->get("/admin/taxes/{$duty->id}/edit")->assertOk();

        $this->actingAs($this->admin)->put("/admin/taxes/{$duty->id}", ['name' => 'Customs Duty', 'status' => '0'])
            ->assertSessionHasNoErrors();
        $this->assertSame('Customs Duty', $duty->fresh()->name);
        $this->assertFalse($duty->fresh()->status);
    }

    public function test_a_product_is_charged_under_a_tax(): void
    {
        $product = Product::orderBy('id')->firstOrFail();

        $this->actingAs($this->admin)->post('/admin/taxes/product-taxes', [
            'product_id' => $product->id, 'tax_id' => $this->vat()->id, 'tax' => '15', 'tax_type' => 'percent',
        ])->assertRedirect('/admin/taxes')->assertSessionHasNoErrors();

        $row = ProductTax::where('product_id', $product->id)->firstOrFail();
        $this->assertSame('15.00', (string) $row->tax);
        $this->assertSame('percent', $row->tax_type);
    }

    public function test_the_same_product_cannot_be_charged_twice_under_one_tax(): void
    {
        $product = Product::orderBy('id')->firstOrFail();
        ProductTax::create(['product_id' => $product->id, 'tax_id' => $this->vat()->id, 'tax' => 15, 'tax_type' => 'percent']);

        $this->actingAs($this->admin)->post('/admin/taxes/product-taxes', [
            'product_id' => $product->id, 'tax_id' => $this->vat()->id, 'tax' => '10', 'tax_type' => 'percent',
        ])->assertRedirect()->assertSessionHas('error', 'This product is already charged under VAT.');
    }

    public function test_an_assignment_can_be_edited_and_removed(): void
    {
        $product = Product::orderBy('id')->firstOrFail();
        $row = ProductTax::create(['product_id' => $product->id, 'tax_id' => $this->vat()->id, 'tax' => 15, 'tax_type' => 'percent']);

        $this->actingAs($this->admin)->get("/admin/taxes/product-taxes/{$row->id}/edit")
            ->assertOk()->assertSee($product->name);

        $this->actingAs($this->admin)->put("/admin/taxes/product-taxes/{$row->id}", [
            'product_id' => $product->id, 'tax_id' => $this->vat()->id, 'tax' => '12.5', 'tax_type' => 'percent',
        ])->assertSessionHasNoErrors();
        $this->assertSame('12.50', (string) $row->fresh()->tax);

        $this->actingAs($this->admin)->delete("/admin/taxes/product-taxes/{$row->id}")
            ->assertRedirect('/admin/taxes')->assertSessionHas('status');
        $this->assertNull($row->fresh());
    }

    public function test_a_tax_in_use_cannot_be_deleted(): void
    {
        $product = Product::orderBy('id')->firstOrFail();
        $vat = $this->vat();
        ProductTax::create(['product_id' => $product->id, 'tax_id' => $vat->id, 'tax' => 15, 'tax_type' => 'percent']);

        $this->actingAs($this->admin)->delete("/admin/taxes/{$vat->id}")
            ->assertRedirect()
            ->assertSessionHas('error', '1 product is assigned this tax; remove those assignments first.');
        $this->assertNotNull($vat->fresh());

        ProductTax::firstOrFail()->delete();
        $this->actingAs($this->admin)->delete("/admin/taxes/{$vat->id}")
            ->assertRedirect('/admin/taxes')->assertSessionHas('status');
        $this->assertNull($vat->fresh());
        $this->assertSame(1, Tax::count());
    }
}
