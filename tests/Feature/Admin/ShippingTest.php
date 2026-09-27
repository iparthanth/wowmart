<?php

namespace Tests\Feature\Admin;

use App\Models\BusinessSetting;
use App\Models\Carrier;
use App\Models\CarrierRange;
use App\Models\CarrierRangePrice;
use App\Models\City;
use App\Models\Country;
use App\Models\State;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ShippingTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::where('email', 'admin@wowmart.test')->firstOrFail();
    }

    // ------------------------------------------------------------- countries

    public function test_countries_list_seeded_rows_and_the_add_form(): void
    {
        $this->actingAs($this->admin)->get('/admin/shipping-countries')
            ->assertOk()
            ->assertSee('Add a country')
            ->assertSee('Bangladesh')
            ->assertSee('United States');
        $this->assertSame(4, Country::count());
    }

    public function test_a_country_is_created_edited_and_deleted(): void
    {
        $this->actingAs($this->admin)->post('/admin/shipping-countries', [
            'name' => 'Nepal', 'code' => 'np', 'status' => '1',
        ])->assertRedirect('/admin/shipping-countries')->assertSessionHasNoErrors();

        $country = Country::where('code', 'NP')->firstOrFail();
        $this->assertSame('Nepal', $country->name);

        $this->actingAs($this->admin)->get("/admin/shipping-countries/{$country->id}/edit")
            ->assertOk()->assertSee('value="Nepal"', false);

        $this->actingAs($this->admin)->put("/admin/shipping-countries/{$country->id}", [
            'name' => 'Nepal Republic', 'code' => 'NPL', 'status' => '0',
        ])->assertSessionHasNoErrors();
        $this->assertSame('Nepal Republic', $country->fresh()->name);
        $this->assertFalse($country->fresh()->status);

        $this->actingAs($this->admin)->delete("/admin/shipping-countries/{$country->id}")
            ->assertRedirect('/admin/shipping-countries')->assertSessionHas('status');
        $this->assertNull($country->fresh());
    }

    public function test_a_country_with_states_cannot_be_deleted(): void
    {
        $country = Country::where('code', 'BD')->firstOrFail();

        $this->actingAs($this->admin)->delete("/admin/shipping-countries/{$country->id}")
            ->assertRedirect()->assertSessionHas('error');
        $this->assertNotNull($country->fresh());
    }

    public static function invalidCountries(): array
    {
        return [
            'no name' => [['name' => '']],
            'no code' => [['code' => '']],
            'bad code' => [['code' => 'bd1']],
            'duplicate code' => [['code' => 'BD']],
        ];
    }

    #[DataProvider('invalidCountries')]
    public function test_a_bad_country_is_refused(array $overrides): void
    {
        $this->actingAs($this->admin)->post('/admin/shipping-countries', array_merge([
            'name' => 'Somewhere', 'code' => 'ZZ', 'status' => '1',
        ], $overrides))->assertSessionHasErrors();

        $this->assertSame(4, Country::count());
    }

    // ---------------------------------------------------------------- states

    public function test_states_list_is_filtered_by_country(): void
    {
        $bd = Country::where('code', 'BD')->firstOrFail();

        $this->actingAs($this->admin)->get('/admin/shipping-states')
            ->assertOk()->assertSee('Dhaka')->assertSee('West Bengal');

        $this->actingAs($this->admin)->get("/admin/shipping-states?country={$bd->id}")
            ->assertOk()->assertSee('Dhaka')->assertDontSee('West Bengal');
    }

    public function test_a_state_is_created_edited_and_deleted(): void
    {
        $bd = Country::where('code', 'BD')->firstOrFail();

        $this->actingAs($this->admin)->post('/admin/shipping-states', [
            'country_id' => $bd->id, 'name' => 'Sylhet', 'status' => '1',
        ])->assertRedirect('/admin/shipping-states')->assertSessionHasNoErrors();

        $state = State::where('name', 'Sylhet')->firstOrFail();

        $this->actingAs($this->admin)->put("/admin/shipping-states/{$state->id}", [
            'country_id' => $bd->id, 'name' => 'Sylhet Division', 'status' => '0',
        ])->assertSessionHasNoErrors();
        $this->assertSame('Sylhet Division', $state->fresh()->name);

        $this->actingAs($this->admin)->delete("/admin/shipping-states/{$state->id}")
            ->assertRedirect('/admin/shipping-states')->assertSessionHas('status');
        $this->assertNull($state->fresh());
    }

    public function test_a_state_with_cities_cannot_be_deleted(): void
    {
        $state = State::where('name', 'Dhaka')->firstOrFail();

        $this->actingAs($this->admin)->delete("/admin/shipping-states/{$state->id}")
            ->assertRedirect()->assertSessionHas('error');
        $this->assertNotNull($state->fresh());
    }

    public function test_a_duplicate_state_name_in_one_country_is_refused(): void
    {
        $bd = Country::where('code', 'BD')->firstOrFail();

        $this->actingAs($this->admin)->post('/admin/shipping-states', [
            'country_id' => $bd->id, 'name' => 'Dhaka', 'status' => '1',
        ])->assertSessionHasErrors();
        $this->assertSame(7, State::count()); // the seeded tree has exactly seven
    }

    // ----------------------------------------------------------------- cities

    public function test_cities_list_is_filtered_by_state(): void
    {
        $state = State::where('name', 'Dhaka')->firstOrFail();

        $this->actingAs($this->admin)->get('/admin/shipping-cities')
            ->assertOk()->assertSee('Dhaka City')->assertSee('Kolkata');

        $this->actingAs($this->admin)->get("/admin/shipping-cities?state={$state->id}")
            ->assertOk()->assertSee('Dhaka City')->assertDontSee('Kolkata');
    }

    public function test_a_city_is_created_edited_and_deleted(): void
    {
        $state = State::where('name', 'Dhaka')->firstOrFail();

        $this->actingAs($this->admin)->post('/admin/shipping-cities', [
            'state_id' => $state->id, 'name' => 'Keraniganj', 'cost' => '75', 'status' => '1',
        ])->assertRedirect('/admin/shipping-cities')->assertSessionHasNoErrors();

        $city = City::where('name', 'Keraniganj')->firstOrFail();
        $this->assertSame('75.00', $city->cost);

        $this->actingAs($this->admin)->put("/admin/shipping-cities/{$city->id}", [
            'state_id' => $state->id, 'name' => 'Keraniganj', 'cost' => '90', 'status' => '0',
        ])->assertSessionHasNoErrors();
        $this->assertSame('90.00', $city->fresh()->cost);

        $this->actingAs($this->admin)->delete("/admin/shipping-cities/{$city->id}")
            ->assertRedirect('/admin/shipping-cities')->assertSessionHas('status');
        $this->assertNull($city->fresh());
    }

    public function test_a_city_without_a_cost_defaults_to_free(): void
    {
        $state = State::where('name', 'England')->firstOrFail();

        $this->actingAs($this->admin)->post('/admin/shipping-cities', [
            'state_id' => $state->id, 'name' => 'Manchester', 'status' => '1',
        ])->assertSessionHasNoErrors();

        $this->assertSame('0.00', City::where('name', 'Manchester')->value('cost'));
    }

    // ----------------------------------------------------------------- zones

    public function test_a_zone_is_created_with_countries_replaced_and_deleted(): void
    {
        $bd = Country::where('code', 'BD')->firstOrFail();
        $us = Country::where('code', 'US')->firstOrFail();
        $gb = Country::where('code', 'GB')->firstOrFail();

        $this->actingAs($this->admin)->post('/admin/shipping-zones', [
            'name' => 'Test Zone', 'countries' => [$bd->id, $us->id], 'status' => '1',
        ])->assertRedirect('/admin/shipping-zones')->assertSessionHasNoErrors();

        $zone = Zone::where('name', 'Test Zone')->firstOrFail();
        $this->assertSame(2, $zone->countries()->count());

        // A save replaces the country list wholesale.
        $this->actingAs($this->admin)->put("/admin/shipping-zones/{$zone->id}", [
            'name' => 'Test Zone', 'countries' => [$gb->id], 'status' => '1',
        ])->assertSessionHasNoErrors();
        $this->assertSame([$gb->id], $zone->countries()->pluck('countries.id')->all());

        $this->actingAs($this->admin)->delete("/admin/shipping-zones/{$zone->id}")
            ->assertRedirect('/admin/shipping-zones')->assertSessionHas('status');
        $this->assertNull($zone->fresh());
    }

    public function test_a_zone_priced_by_a_carrier_cannot_be_deleted(): void
    {
        $zone = Zone::where('name', 'South Asia')->firstOrFail();

        $this->actingAs($this->admin)->delete("/admin/shipping-zones/{$zone->id}")
            ->assertRedirect()->assertSessionHas('error');
        $this->assertNotNull($zone->fresh());
    }

    // -------------------------------------------------------------- carriers

    public function test_carriers_list_seeded_rows_and_the_add_form(): void
    {
        $this->actingAs($this->admin)->get('/admin/shipping-carriers')
            ->assertOk()
            ->assertSee('Add a carrier')
            ->assertSee('Steady Ship')
            ->assertSee('FreeFly');
        $this->assertSame(2, Carrier::count());
    }

    public function test_a_carrier_is_created_edited_and_deleted(): void
    {
        $this->actingAs($this->admin)->post('/admin/shipping-carriers', [
            'name' => 'Rocket Courier', 'transit_time' => '1-2 days', 'free_shipping' => '0', 'status' => '1',
        ])->assertRedirect('/admin/shipping-carriers')->assertSessionHasNoErrors();

        $carrier = Carrier::where('name', 'Rocket Courier')->firstOrFail();

        $this->actingAs($this->admin)->put("/admin/shipping-carriers/{$carrier->id}", [
            'name' => 'Rocket Courier', 'transit_time' => '1-3 days', 'free_shipping' => '1', 'status' => '1',
        ])->assertSessionHasNoErrors();
        $this->assertTrue($carrier->fresh()->free_shipping);

        $this->actingAs($this->admin)->delete("/admin/shipping-carriers/{$carrier->id}")
            ->assertRedirect('/admin/shipping-carriers')->assertSessionHas('status');
        $this->assertNull($carrier->fresh());
    }

    public function test_a_carrier_range_and_zone_prices_are_managed_on_the_edit_page(): void
    {
        $carrier = Carrier::where('name', 'Steady Ship')->firstOrFail();
        $gb = Country::where('code', 'GB')->firstOrFail();
        $zone = Zone::create(['name' => 'Test Range Zone', 'status' => true]);
        $zone->countries()->sync([$gb->id]);

        $this->actingAs($this->admin)->get("/admin/shipping-carriers/{$carrier->id}/edit")
            ->assertOk()->assertSee('Steady Ship')->assertSee('South Asia');

        // Add a range ending at 5 kg.
        $this->actingAs($this->admin)->post("/admin/shipping-carriers/{$carrier->id}/ranges", [
            'billing_type' => 'weight', 'lower' => '1', 'upper' => '5',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $range = CarrierRange::where('carrier_id', $carrier->id)->where('lower', '1')->where('upper', '5')->firstOrFail();

        // Price it for the new zone.
        $this->actingAs($this->admin)->post("/admin/shipping-carriers/carrier-ranges/{$range->id}/prices", [
            'zone_id' => $zone->id, 'price' => '120',
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame('120.00', (string) $range->prices()->where('zone_id', $zone->id)->value('price'));

        // Setting the same zone again replaces the price in place.
        $this->actingAs($this->admin)->post("/admin/shipping-carriers/carrier-ranges/{$range->id}/prices", [
            'zone_id' => $zone->id, 'price' => '140',
        ])->assertSessionHasNoErrors();
        $this->assertSame(1, CarrierRangePrice::where('carrier_range_id', $range->id)->where('zone_id', $zone->id)->count());
        $this->assertSame('140.00', (string) $range->prices()->where('zone_id', $zone->id)->value('price'));

        // Deleting the range takes its prices with it.
        $this->actingAs($this->admin)->delete("/admin/shipping-carriers/carrier-ranges/{$range->id}")
            ->assertRedirect()->assertSessionHas('status');
        $this->assertSame(0, CarrierRangePrice::where('carrier_range_id', $range->id)->count());
    }

    public static function invalidRanges(): array
    {
        return [
            'no billing type' => [['billing_type' => '']],
            'bad billing type' => [['billing_type' => 'mass']],
            'negative lower' => [['lower' => '-1']],
            'upper before lower' => [['lower' => '5', 'upper' => '1']],
        ];
    }

    #[DataProvider('invalidRanges')]
    public function test_a_bad_range_is_refused(array $overrides): void
    {
        $carrier = Carrier::where('name', 'Steady Ship')->firstOrFail();

        $this->actingAs($this->admin)->post("/admin/shipping-carriers/{$carrier->id}/ranges", array_merge([
            'billing_type' => 'weight', 'lower' => '0', 'upper' => '2',
        ], $overrides))->assertSessionHasErrors();

        $this->assertSame(2, CarrierRange::where('carrier_id', $carrier->id)->count()); // only the seeded two
    }

    // ------------------------------------------------------------- settings

    public function test_the_shipping_configuration_screen_saves_its_keys(): void
    {
        $this->actingAs($this->admin)->get('/admin/settings/shipping')
            ->assertOk()->assertSee('Shipping Configuration');

        $this->actingAs($this->admin)->put('/admin/settings/shipping', [
            'shipping_method' => 'area_wise', 'flat_shipping_cost' => '75',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertSame('area_wise', BusinessSetting::get('shipping_method'));
        $this->assertSame('75', BusinessSetting::get('flat_shipping_cost'));
    }
}
