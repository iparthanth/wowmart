<?php

namespace App\Services;

use App\Models\CarrierRangePrice;
use App\Models\Zone;
use Illuminate\Support\Facades\DB;

/**
 * Every write to a shipping zone. The country list is synced on every save,
 * so a zone always mirrors exactly the countries the form sent.
 */
class ZoneService
{
    /** @param list<int> $countryIds */
    public function create(array $data, array $countryIds): Zone
    {
        return DB::transaction(function () use ($data, $countryIds) {
            $zone = Zone::create($data);
            $zone->countries()->sync($countryIds);

            return $zone;
        });
    }

    /** @param list<int> $countryIds */
    public function update(Zone $zone, array $data, array $countryIds): Zone
    {
        return DB::transaction(function () use ($zone, $data, $countryIds) {
            tap($zone)->update($data);
            $zone->countries()->sync($countryIds);

            return $zone;
        });
    }

    /** @throws \DomainException while a carrier range still prices this zone */
    public function delete(Zone $zone): void
    {
        $prices = CarrierRangePrice::where('zone_id', $zone->id)->count();

        if ($prices > 0) {
            throw new \DomainException(sprintf(
                'Carrier ranges still price %s; remove those prices first.',
                $zone->name,
            ));
        }

        $zone->delete();
    }
}
