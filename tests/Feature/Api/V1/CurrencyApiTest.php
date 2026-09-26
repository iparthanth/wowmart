<?php

namespace Tests\Feature\Api\V1;

use App\Models\BusinessSetting;
use App\Models\Currency;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CurrencyApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Sanctum::actingAs(User::where('email', 'admin@wowmart.test')->firstOrFail());
    }

    public function test_the_list_is_paginated_with_the_seeded_four(): void
    {
        $this->getJson('/api/v1/currencies?per_page=3')
            ->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('meta.total', 4)
            ->assertJsonPath('data.0.name', 'Bangladeshi Taka')
            ->assertJsonPath('data.0.code', 'BDT')
            ->assertJsonPath('data.0.exchange_rate', 117)
            ->assertJsonPath('data.0.status', true);
    }

    public function test_a_currency_is_created_and_replaced(): void
    {
        $id = $this->postJson('/api/v1/currencies', [
            'name' => 'Australian Dollar', 'symbol' => 'A$', 'code' => 'aud', 'exchange_rate' => '1.52',
        ])->assertCreated()->assertJsonPath('data.code', 'AUD')->json('data.id');

        $this->postJson('/api/v1/currencies', ['name' => 'Again', 'symbol' => '$', 'code' => 'USD', 'exchange_rate' => '1'])
            ->assertUnprocessable()->assertJsonPath('errors.code.0', 'Another currency already uses this code. Edit that currency instead.');

        $this->postJson('/api/v1/currencies', ['name' => 'Bad', 'symbol' => 'B', 'code' => 'US1', 'exchange_rate' => '1'])
            ->assertUnprocessable()->assertJsonPath('errors.code.0', 'Enter the ISO code as exactly three letters, like USD.');

        $this->postJson('/api/v1/currencies', ['name' => 'Zero', 'symbol' => 'Z', 'code' => 'ZRO', 'exchange_rate' => '0'])
            ->assertUnprocessable()->assertJsonPath('errors.exchange_rate.0', 'The exchange rate must be greater than zero.');

        $this->putJson("/api/v1/currencies/{$id}", [
            'name' => 'Australian Dollar', 'symbol' => 'A$', 'code' => 'AUD', 'exchange_rate' => '1.55', 'status' => false,
        ])->assertOk()->assertJsonPath('data.exchange_rate', 1.55)->assertJsonPath('data.status', false);

        $this->patchJson("/api/v1/currencies/{$id}", ['name' => 'x'])->assertMethodNotAllowed();
    }

    public function test_the_system_currency_is_409_on_delete(): void
    {
        $usd = Currency::where('code', 'USD')->firstOrFail();
        $eur = Currency::where('code', 'EUR')->firstOrFail();

        $this->deleteJson("/api/v1/currencies/{$usd->id}")
            ->assertConflict()
            ->assertJsonPath('message', 'This is the system currency. Choose another currency as system currency first.');

        $this->deleteJson("/api/v1/currencies/{$eur->id}")->assertNoContent();
        $this->assertSame(3, Currency::count());
        $this->assertSame((string) $usd->id, (string) BusinessSetting::get('system_currency'));
    }
}
