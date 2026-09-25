<?php

namespace Tests\Feature\Api\V1;

use App\Models\Color;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ColorApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Sanctum::actingAs(User::where('email', 'admin@wowmart.test')->firstOrFail());
    }

    public function test_the_list_is_paginated(): void
    {
        $this->getJson('/api/v1/colors?per_page=2')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.total', 5)
            ->assertJsonPath('data.0', ['id' => Color::where('name', 'Black')->value('id'), 'name' => 'Black', 'code' => '#1B1B1B']);
    }

    public function test_create_uppercases_and_validates_the_code(): void
    {
        $id = $this->postJson('/api/v1/colors', ['name' => 'Teal', 'code' => '#0a9396'])
            ->assertCreated()->assertJsonPath('data.code', '#0A9396')->json('data.id');

        $this->postJson('/api/v1/colors', ['name' => 'Other teal', 'code' => '#0A9396'])->assertUnprocessable()->assertJsonValidationErrors('code');
        $this->postJson('/api/v1/colors', ['name' => 'Bad', 'code' => '0A9397'])
            ->assertUnprocessable()->assertJsonPath('errors.code.0', 'Enter the code as # and six hex digits, like #1E5AA8.');
        $this->postJson('/api/v1/colors', ['code' => '#000001'])->assertUnprocessable()->assertJsonValidationErrors('name');

        $this->putJson("/api/v1/colors/{$id}", ['name' => 'Deep teal', 'code' => '#005f73'])->assertOk()->assertJsonPath('data.code', '#005F73');
        $this->patchJson("/api/v1/colors/{$id}", ['name' => 'x'])->assertMethodNotAllowed();
        $this->getJson("/api/v1/colors/{$id}")->assertOk()->assertJsonPath('data.name', 'Deep teal');
    }

    public function test_a_colour_in_use_is_409_on_delete_and_recode(): void
    {
        $red = Color::where('name', 'Red')->firstOrFail();
        Product::orderBy('id')->take(2)->get()->each(fn ($p) => $p->forceFill(['colors' => ['#D7263D', '#1B1B1B']])->save());

        $this->deleteJson("/api/v1/colors/{$red->id}")
            ->assertConflict()->assertJsonPath('message', '2 products offer this colour; remove it from them first.');
        $this->putJson("/api/v1/colors/{$red->id}", ['name' => 'Red', 'code' => '#FF0000'])->assertConflict();
        $this->assertSame('#D7263D', $red->fresh()->code);

        $blue = Color::where('name', 'Blue')->firstOrFail();
        $this->deleteJson("/api/v1/colors/{$blue->id}")->assertNoContent();
        $this->assertSame(4, Color::count());
    }
}
