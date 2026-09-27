<?php

namespace Database\Seeders;

use App\Models\Carrier;
use App\Models\CarrierRange;
use App\Models\City;
use App\Models\Country;
use App\Models\State;
use App\Models\Zone;
use Illuminate\Database\Seeder;

/**
 * The shipping reference rows, deterministic like the other seeders so tests
 * can rely on exact numbers:
 *
 * - four countries (BD, IN, US, GB) with the states and costed cities the
 *   shipping lists show;
 * - two zones: "South Asia" (BD, IN) and "North America" (US);
 * - two carriers: "Steady Ship" with a weight range priced per zone, and
 *   "FreeFly" marked free shipping with no billing setup.
 */
class ShippingSeeder extends Seeder
{
    /** country => [code, [state => [[city, cost], ...]]] */
    private const TREE = [
        'Bangladesh' => ['BD', [
            'Dhaka' => [['Dhaka City', 60], ['Savar', 80], ['Gazipur', 80]],
            'Chattogram' => [['Chattogram City', 70], ['Cox\'s Bazar', 120]],
        ]],
        'India' => ['IN', [
            'West Bengal' => [['Kolkata', 90]],
            'Maharashtra' => [['Mumbai', 110], ['Pune', 110]],
        ]],
        'United States' => ['US', [
            'New York' => [['New York City', 900]],
            'California' => [['Los Angeles', 950], ['San Diego', 950]],
        ]],
        'United Kingdom' => ['GB', [
            'England' => [['London', 850]],
        ]],
    ];

    public function run(): void
    {
        foreach (self::TREE as $name => [$code, $states]) {
            $country = Country::firstOrCreate(['code' => $code], ['name' => $name, 'code' => $code, 'status' => true]);

            foreach ($states as $stateName => $cities) {
                $state = State::firstOrCreate(
                    ['country_id' => $country->id, 'name' => $stateName],
                    ['status' => true],
                );

                foreach ($cities as [$cityName, $cost]) {
                    City::firstOrCreate(
                        ['state_id' => $state->id, 'name' => $cityName],
                        ['cost' => $cost, 'status' => true],
                    );
                }
            }
        }

        $southAsia = Zone::firstOrCreate(['name' => 'South Asia'], ['name' => 'South Asia', 'status' => true]);
        $southAsia->countries()->syncWithoutDetaching(
            Country::whereIn('code', ['BD', 'IN'])->pluck('id')
        );

        $northAmerica = Zone::firstOrCreate(['name' => 'North America'], ['name' => 'North America', 'status' => true]);
        $northAmerica->countries()->syncWithoutDetaching(
            Country::whereIn('code', ['US'])->pluck('id')
        );

        $steady = Carrier::firstOrCreate(['name' => 'Steady Ship'], [
            'name' => 'Steady Ship', 'transit_time' => '2-4 days', 'free_shipping' => false, 'status' => true,
        ]);

        $light = CarrierRange::firstOrCreate(
            ['carrier_id' => $steady->id, 'billing_type' => 'weight', 'lower' => 0, 'upper' => 1],
            ['billing_type' => 'weight', 'lower' => 0, 'upper' => 1],
        );
        $light->prices()->updateOrCreate(['zone_id' => $southAsia->id], ['price' => 50]);
        $light->prices()->updateOrCreate(['zone_id' => $northAmerica->id], ['price' => 400]);

        $heavy = CarrierRange::firstOrCreate(
            ['carrier_id' => $steady->id, 'billing_type' => 'weight', 'lower' => 1, 'upper' => null],
            ['billing_type' => 'weight', 'lower' => 1, 'upper' => null],
        );
        $heavy->prices()->updateOrCreate(['zone_id' => $southAsia->id], ['price' => 90]);
        $heavy->prices()->updateOrCreate(['zone_id' => $northAmerica->id], ['price' => 700]);

        Carrier::firstOrCreate(['name' => 'FreeFly'], [
            'name' => 'FreeFly', 'transit_time' => '5-8 days', 'free_shipping' => true, 'status' => true,
        ]);
    }
}
