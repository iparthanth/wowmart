<?php

namespace Tests\Feature\Admin;

use App\Models\BusinessSetting;
use App\Models\Currency;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CurrencyTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::where('email', 'admin@wowmart.test')->firstOrFail();
    }

    private function usd(): Currency
    {
        return Currency::where('code', 'USD')->firstOrFail();
    }

    public function test_the_list_shows_the_four_seeded_currencies_with_the_system_one_marked(): void
    {
        $this->actingAs($this->admin)->get('/admin/currencies')
            ->assertOk()
            ->assertSee('Add a currency')
            ->assertSeeInOrder(['Bangladeshi Taka', 'British Pound', 'Euro', 'US Dollar'])
            ->assertSee('system')
            ->assertViewHas('currencies', fn ($page) => $page->total() === 4);
    }

    public function test_a_currency_is_created_with_an_uppercase_iso_code(): void
    {
        $this->actingAs($this->admin)->post('/admin/currencies', [
            'name' => 'Australian Dollar', 'symbol' => 'A$', 'code' => 'aud', 'exchange_rate' => '1.52', 'status' => '1',
        ])->assertRedirect('/admin/currencies')->assertSessionHasNoErrors();

        $this->assertSame('AUD', Currency::where('name', 'Australian Dollar')->value('code'));
    }

    public static function badCodes(): array
    {
        return [
            'two letters' => ['US'],
            'four letters' => ['USDX'],
            'digits' => ['US1'],
            'array' => [['USD']],
        ];
    }

    #[DataProvider('badCodes')]
    public function test_a_code_that_is_not_three_letters_is_refused(mixed $code): void
    {
        $this->actingAs($this->admin)->post('/admin/currencies', [
            'name' => 'X', 'symbol' => 'X', 'code' => $code, 'exchange_rate' => '1',
        ])->assertSessionHasErrors('code');

        $this->assertSame(4, Currency::count());
    }

    public function test_a_code_is_unique(): void
    {
        $this->actingAs($this->admin)->post('/admin/currencies', [
            'name' => 'Dollar Again', 'symbol' => '$', 'code' => 'USD', 'exchange_rate' => '1',
        ])->assertSessionHasErrors('code');

        $this->assertSame(4, Currency::count());
    }

    public function test_a_zero_rate_is_refused(): void
    {
        $this->actingAs($this->admin)->post('/admin/currencies', [
            'name' => 'Zero', 'symbol' => 'Z', 'code' => 'ZRO', 'exchange_rate' => '0',
        ])->assertSessionHasErrors('exchange_rate');
    }

    public function test_the_edit_page_opens_and_saves(): void
    {
        $eur = Currency::where('code', 'EUR')->firstOrFail();

        $this->actingAs($this->admin)->get("/admin/currencies/{$eur->id}/edit")
            ->assertOk()->assertSee('value="€"', false);

        $this->actingAs($this->admin)->put("/admin/currencies/{$eur->id}", [
            'name' => 'Euro', 'symbol' => '€', 'code' => 'EUR', 'exchange_rate' => '0.95', 'status' => '1',
        ])->assertSessionHasNoErrors();
        $this->assertSame('0.950000', (string) $eur->fresh()->exchange_rate);
    }

    public function test_the_system_currency_cannot_be_deleted_or_switched_off(): void
    {
        $usd = $this->usd();

        $this->actingAs($this->admin)->delete("/admin/currencies/{$usd->id}")
            ->assertRedirect()
            ->assertSessionHas('error', 'This is the system currency. Choose another currency as system currency first.');
        $this->assertNotNull($usd->fresh());
    }

    public function test_another_currency_can_be_made_system_and_then_the_old_one_deleted(): void
    {
        $eur = Currency::where('code', 'EUR')->firstOrFail();
        $usd = $this->usd();

        $this->actingAs($this->admin)->post("/admin/currencies/{$eur->id}/system")
            ->assertRedirect('/admin/currencies')->assertSessionHas('status');
        $this->assertSame((string) $eur->id, (string) BusinessSetting::get('system_currency'));

        $this->actingAs($this->admin)->delete("/admin/currencies/{$usd->id}")
            ->assertRedirect('/admin/currencies')->assertSessionHas('status');
        $this->assertNull($usd->fresh());
        $this->assertSame(3, Currency::count());
    }

    public function test_a_disabled_currency_cannot_become_the_system_currency(): void
    {
        $this->actingAs($this->admin)->post('/admin/currencies', [
            'name' => 'Disabled', 'symbol' => 'D', 'code' => 'DIS', 'exchange_rate' => '2', 'status' => '0',
        ])->assertSessionHasNoErrors();

        $dis = Currency::where('code', 'DIS')->firstOrFail();
        $this->actingAs($this->admin)->post("/admin/currencies/{$dis->id}/system")
            ->assertRedirect()
            ->assertSessionHas('error', 'Enable the currency before making it the system currency.');
    }
}
