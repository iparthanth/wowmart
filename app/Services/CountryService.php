<?php

namespace App\Services;

use App\Models\Country;
use Illuminate\Support\Facades\DB;

/** Every write to a shipping country. Input is validated already (CountryRequest). */
class CountryService
{
    public function create(array $data): Country
    {
        return DB::transaction(fn () => Country::create($data));
    }

    public function update(Country $country, array $data): Country
    {
        return DB::transaction(fn () => tap($country)->update($data));
    }

    /** @throws \DomainException while the country still has states below it */
    public function delete(Country $country): void
    {
        $states = $country->states()->count();

        if ($states > 0) {
            throw new \DomainException(sprintf(
                '%s still has %s below it. Delete them first.',
                $country->name,
                $states === 1 ? '1 state' : $states.' states',
            ));
        }

        $country->delete();
    }
}
