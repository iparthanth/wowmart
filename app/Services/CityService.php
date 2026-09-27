<?php

namespace App\Services;

use App\Models\City;
use Illuminate\Support\Facades\DB;

/** Every write to a shipping city. Input is validated already (CityRequest). */
class CityService
{
    public function create(array $data): City
    {
        return DB::transaction(fn () => City::create($data));
    }

    public function update(City $city, array $data): City
    {
        return DB::transaction(fn () => tap($city)->update($data));
    }

    /** A city can always be deleted: nothing outside its own state references it. */
    public function delete(City $city): void
    {
        $city->delete();
    }
}
