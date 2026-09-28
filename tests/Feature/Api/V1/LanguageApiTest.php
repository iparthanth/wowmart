<?php

namespace Tests\Feature\Api\V1;

use App\Models\Language;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class LanguageApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Sanctum::actingAs(User::where('email', 'admin@wowmart.test')->firstOrFail());
    }

    public function test_a_language_round_trips(): void
    {
        $id = $this->postJson('/api/v1/languages', [
            'name' => 'Français', 'code' => 'fr', 'rtl' => false, 'status' => true,
        ])->assertCreated()
            ->assertJsonPath('data.code', 'fr')
            ->assertJsonPath('data.rtl', false)
            ->json('data.id');

        $this->putJson("/api/v1/languages/{$id}", [
            'name' => 'Français', 'code' => 'fr', 'rtl' => true, 'status' => true,
        ])->assertOk()->assertJsonPath('data.rtl', true);

        $this->patchJson("/api/v1/languages/{$id}", ['name' => 'x'])->assertMethodNotAllowed();

        $this->deleteJson("/api/v1/languages/{$id}")->assertNoContent();
        $this->assertSame(0, Language::where('code', 'fr')->count());
    }

    public function test_a_duplicate_code_is_422_and_the_default_language_is_409(): void
    {
        $this->postJson('/api/v1/languages', ['name' => 'English again', 'code' => 'en'])
            ->assertUnprocessable();

        $english = Language::where('code', 'en')->firstOrFail();
        $this->deleteJson("/api/v1/languages/{$english->id}")->assertConflict();
    }

    public function test_languages_need_an_admin(): void
    {
        Sanctum::actingAs(User::where('email', 'rafi@customers.wowmart.test')->firstOrFail());

        $this->postJson('/api/v1/languages', ['name' => 'X', 'code' => 'xx'])->assertForbidden();
    }
}
