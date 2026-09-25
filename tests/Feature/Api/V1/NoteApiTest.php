<?php

namespace Tests\Feature\Api\V1;

use App\Models\Note;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class NoteApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Sanctum::actingAs(User::where('email', 'admin@wowmart.test')->firstOrFail());
    }

    public function test_the_list_filters_by_type(): void
    {
        $refund = Note::where('type', 'refund')->firstOrFail();
        Product::orderBy('id')->firstOrFail()->forceFill(['refund_note_id' => $refund->id])->save();

        $this->getJson('/api/v1/notes')->assertOk()->assertJsonPath('meta.total', 2)
            ->assertJsonStructure(['data' => [['id', 'type', 'type_label', 'description', 'product_count', 'created_at', 'updated_at']]]);

        $this->getJson('/api/v1/notes?type=refund')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.type', 'refund')
            ->assertJsonPath('data.0.type_label', 'Refund')
            ->assertJsonPath('data.0.product_count', 1);

        $this->getJson('/api/v1/notes?type=nope')->assertOk()->assertJsonPath('meta.total', 2);
        $this->getJson('/api/v1/notes?type[]=refund')->assertOk()->assertJsonPath('meta.total', 2);
    }

    public function test_create_and_replace_keep_the_type(): void
    {
        $id = $this->postJson('/api/v1/notes', ['type' => 'warranty', 'description' => 'Battery: 1 year.'])
            ->assertCreated()->assertJsonPath('data.type', 'warranty')->assertJsonPath('data.product_count', 0)->json('data.id');

        $this->putJson("/api/v1/notes/{$id}", ['type' => 'refund', 'description' => 'x'])
            ->assertUnprocessable()->assertJsonValidationErrors('type');
        $this->putJson("/api/v1/notes/{$id}", ['type' => 'warranty', 'description' => 'Battery: 2 years.'])
            ->assertOk()->assertJsonPath('data.description', 'Battery: 2 years.');
        $this->patchJson("/api/v1/notes/{$id}", ['description' => 'x'])->assertMethodNotAllowed();

        $this->assertSame('warranty', Note::findOrFail($id)->type);
        $this->postJson('/api/v1/notes', ['description' => 'no type'])->assertUnprocessable()->assertJsonValidationErrors('type');
    }

    public function test_a_note_in_use_is_409(): void
    {
        $warranty = Note::where('type', 'warranty')->firstOrFail();
        Product::orderBy('id')->firstOrFail()->forceFill(['warranty_note_id' => $warranty->id])->save();

        $this->deleteJson("/api/v1/notes/{$warranty->id}")
            ->assertConflict()->assertJsonPath('message', '1 product uses this note; pick another note on it first.');

        $refund = Note::where('type', 'refund')->firstOrFail();
        $this->deleteJson("/api/v1/notes/{$refund->id}")->assertNoContent();
        $this->assertSame(1, Note::count());
    }
}
