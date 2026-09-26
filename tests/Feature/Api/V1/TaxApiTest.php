<?php

namespace Tests\Feature\Api\V1;

use App\Models\Product;
use App\Models\ProductTax;
use App\Models\Tax;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TaxApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Sanctum::actingAs(User::where('email', 'admin@wowmart.test')->firstOrFail());
    }

    public function test_the_list_is_paginated_with_assignment_counts(): void
    {
        $this->getJson('/api/v1/taxes')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.name', 'GST')
            ->assertJsonPath('data.0.product_tax_count', 0)
            ->assertJsonPath('data.1.name', 'VAT');
    }

    public function test_a_tax_is_created_and_replaced(): void
    {
        $id = $this->postJson('/api/v1/taxes', ['name' => 'Import Duty', 'status' => true])
            ->assertCreated()->assertJsonPath('data.name', 'Import Duty')->json('data.id');

        $this->postJson('/api/v1/taxes', ['name' => 'VAT'])
            ->assertUnprocessable()->assertJsonValidationErrors('name');

        $this->putJson("/api/v1/taxes/{$id}", ['name' => 'Customs Duty', 'status' => false])
            ->assertOk()->assertJsonPath('data.name', 'Customs Duty')->assertJsonPath('data.status', false);

        $this->patchJson("/api/v1/taxes/{$id}", ['name' => 'x'])->assertMethodNotAllowed();
    }

    public function test_a_tax_in_use_is_409_on_delete(): void
    {
        $vat = Tax::where('name', 'VAT')->firstOrFail();
        $gst = Tax::where('name', 'GST')->firstOrFail();
        $product = Product::orderBy('id')->firstOrFail();
        ProductTax::create(['product_id' => $product->id, 'tax_id' => $vat->id, 'tax' => 15, 'tax_type' => 'percent']);

        $this->deleteJson("/api/v1/taxes/{$vat->id}")
            ->assertConflict()
            ->assertJsonPath('message', '1 product is assigned this tax; remove those assignments first.');

        $this->deleteJson("/api/v1/taxes/{$gst->id}")->assertNoContent();
        $this->assertSame(1, Tax::count());
    }
}
