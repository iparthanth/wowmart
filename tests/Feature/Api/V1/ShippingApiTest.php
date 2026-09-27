<?php

namespace Tests\Feature\Api\V1;

use App\Models\Carrier;
use App\Models\CarrierRange;
use App\Models\CarrierRangePrice;
use App\Models\Country;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ShippingApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Sanctum::actingAs(User::where('email', 'admin@wowmart.test')->firstOrFail());
    }

    public function test_a_country_is_created_read_replaced_and_deleted(): void
    {
        $id = $this->postJson('/api/v1/countries', [
            'name' => 'Nepal', 'code' => 'NP', 'status' => true,
        ])->assertCreated()
            ->assertJsonPath('data.name', 'Nepal')
            ->assertJsonPath('data.code', 'NP')
            ->assertJsonPath('data.status', true)
            ->json('data.id');

        $this->getJson("/api/v1/countries/{$id}")->assertOk()->assertJsonPath('data.name', 'Nepal');

        $this->putJson("/api/v1/countries/{$id}", ['name' => 'Nepal Republic', 'code' => 'NPL', 'status' => false])
            ->assertOk()->assertJsonPath('data.name', 'Nepal Republic')->assertJsonPath('data.status', false);

        $this->patchJson("/api/v1/countries/{$id}", ['name' => 'x'])->assertMethodNotAllowed();

        $this->deleteJson("/api/v1/countries/{$id}")->assertNoContent();
        $this->assertSame(4, Country::count()); // the seeded four remain
    }

    public function test_deleting_a_country_that_has_states_is_409(): void
    {
        $country = Country::where('code', 'BD')->firstOrFail();

        $this->deleteJson("/api/v1/countries/{$country->id}")
            ->assertConflict()->assertJsonPath('message', fn ($m) => str_contains($m, 'still has'));
    }

    public function test_states_and_cities_read_with_their_parents(): void
    {
        $this->getJson('/api/v1/states?per_page=100')->assertOk()
            ->assertJsonFragment(['name' => 'Dhaka'])
            ->assertJsonFragment(['name' => 'West Bengal']);

        $this->getJson('/api/v1/cities?per_page=100')->assertOk()
            ->assertJsonFragment(['name' => 'Dhaka City', 'cost' => '60.00'])
            ->assertJsonFragment(['name' => 'Kolkata', 'cost' => '90.00']);
    }

    public function test_a_zone_is_created_with_countries_and_synced_on_replace(): void
    {
        $bd = Country::where('code', 'BD')->firstOrFail();
        $gb = Country::where('code', 'GB')->firstOrFail();

        $id = $this->postJson('/api/v1/zones', [
            'name' => 'API Zone', 'countries' => [$bd->id], 'status' => true,
        ])->assertCreated()
            ->assertJsonPath('data.name', 'API Zone')
            ->assertJsonCount(1, 'data.countries')
            ->json('data.id');

        $this->putJson("/api/v1/zones/{$id}", [
            'name' => 'API Zone', 'countries' => [$gb->id], 'status' => true,
        ])->assertOk()->assertJsonPath('data.countries.0.code', 'GB');
    }

    public function test_a_zone_priced_by_a_carrier_is_409_on_delete(): void
    {
        $zone = Zone::where('name', 'South Asia')->firstOrFail();

        $this->deleteJson("/api/v1/zones/{$zone->id}")
            ->assertConflict()->assertJsonPath('message', fn ($m) => str_contains($m, 'still price'));
    }

    public function test_a_carrier_and_its_billing_setup_round_trip(): void
    {
        $id = $this->postJson('/api/v1/carriers', [
            'name' => 'Rocket Courier', 'transit_time' => '1-2 days', 'free_shipping' => false, 'status' => true,
        ])->assertCreated()->assertJsonPath('data.name', 'Rocket Courier')->json('data.id');

        $rangeId = $this->postJson("/api/v1/carriers/{$id}/ranges", [
            'billing_type' => 'weight', 'lower' => 0, 'upper' => 2,
        ])->assertCreated()
            ->assertJsonPath('data.ranges.0.billing_type', 'weight')
            ->assertJsonPath('data.ranges.0.upper', '2.00')
            ->json('data.ranges.0.id');

        $zone = Zone::where('name', 'South Asia')->firstOrFail();

        $this->postJson("/api/v1/carrier-ranges/{$rangeId}/prices", [
            'zone_id' => $zone->id, 'price' => '65',
        ])->assertCreated()->assertJsonPath('data.ranges.0.prices.0.price', '65.00');

        // Setting the same zone again replaces the stored price.
        $this->postJson("/api/v1/carrier-ranges/{$rangeId}/prices", [
            'zone_id' => $zone->id, 'price' => '80',
        ])->assertCreated()->assertJsonPath('data.ranges.0.prices.0.price', '80.00');
        $this->assertSame(1, CarrierRangePrice::where('carrier_range_id', $rangeId)->count());

        $this->getJson("/api/v1/carriers/{$id}")->assertOk()->assertJsonPath('data.ranges.0.upper', '2.00');

        $this->deleteJson("/api/v1/carrier-ranges/{$rangeId}")->assertNoContent();
        $this->assertSame(0, CarrierRange::where('carrier_id', $id)->count());

        $this->deleteJson("/api/v1/carriers/{$id}")->assertNoContent();
        $this->assertSame(2, Carrier::count()); // the seeded two remain
    }

    public function test_a_bad_country_payload_is_422(): void
    {
        $this->postJson('/api/v1/countries', ['name' => '', 'code' => 'XX'])
            ->assertUnprocessable()->assertJsonValidationErrors('name');

        $this->postJson('/api/v1/countries', ['name' => 'Bad', 'code' => 'TOOLONG'])
            ->assertUnprocessable()->assertJsonValidationErrors('code');
    }
}
