<?php

namespace Tests\Feature\Api\V1;

use App\Models\Product;
use App\Models\User;
use App\Models\Warranty;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class WarrantyApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Sanctum::actingAs(User::where('email', 'admin@wowmart.test')->firstOrFail());
    }

    public function test_the_list_counts_products(): void
    {
        $year = Warranty::where('text', '1 year')->firstOrFail();
        Product::orderBy('id')->firstOrFail()->forceFill(['warranty_id' => $year->id])->save();

        $this->getJson('/api/v1/warranties')
            ->assertOk()
            ->assertJsonPath('meta.total', 3)
            ->assertJsonPath('data.0', ['id' => $year->id, 'text' => '1 year', 'product_count' => 1])
            ->assertJsonPath('data.*.text', ['1 year', '2 years', '6 months']);
    }

    public function test_create_replace_and_uniqueness(): void
    {
        $id = $this->postJson('/api/v1/warranties', ['text' => '3 years'])
            ->assertCreated()->assertJsonPath('data', fn ($d) => $d['text'] === '3 years' && $d['product_count'] === 0)->json('data.id');

        $this->postJson('/api/v1/warranties', ['text' => '3 YEARS'])->assertUnprocessable()->assertJsonValidationErrors('text');
        $this->putJson("/api/v1/warranties/{$id}", ['text' => '36 months'])->assertOk()->assertJsonPath('data.text', '36 months');
        $this->patchJson("/api/v1/warranties/{$id}", ['text' => 'x'])->assertMethodNotAllowed();
    }

    public function test_a_warranty_in_use_is_409(): void
    {
        $year = Warranty::where('text', '1 year')->firstOrFail();
        Product::orderBy('id')->firstOrFail()->forceFill(['warranty_id' => $year->id])->save();

        $this->deleteJson("/api/v1/warranties/{$year->id}")
            ->assertConflict()->assertJsonPath('message', '1 product uses this warranty; pick another warranty on it first.');

        $six = Warranty::where('text', '6 months')->firstOrFail();
        $this->deleteJson("/api/v1/warranties/{$six->id}")->assertNoContent();
        $this->assertSame(2, Warranty::count());
    }
}
