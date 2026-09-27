<?php

namespace App\Services;

use App\Models\Carrier;
use App\Models\CarrierRange;
use App\Models\CarrierRangePrice;
use Illuminate\Support\Facades\DB;

/**
 * Every write to a carrier and its billing setup. Ranges belong to their
 * carrier; a zone price is one row per (range, zone) and is overwritten
 * in place when set again.
 */
class CarrierService
{
    public function create(array $data): Carrier
    {
        return DB::transaction(fn () => Carrier::create($data));
    }

    public function update(Carrier $carrier, array $data): Carrier
    {
        return DB::transaction(fn () => tap($carrier)->update($data));
    }

    /** A carrier can always be deleted: its ranges and zone prices are its own billing setup. */
    public function delete(Carrier $carrier): void
    {
        $carrier->delete();
    }

    public function addRange(Carrier $carrier, array $data): CarrierRange
    {
        return DB::transaction(fn () => $carrier->ranges()->create($data));
    }

    public function deleteRange(CarrierRange $range): void
    {
        $range->delete(); // its zone prices go with it
    }

    /** Sets the price for one zone inside a range, replacing any stored one. */
    public function setRangePrice(CarrierRange $range, array $data): CarrierRangePrice
    {
        return DB::transaction(function () use ($range, $data) {
            $existing = $range->prices()->where('zone_id', $data['zone_id'])->first();

            if ($existing) {
                $existing->update(['price' => $data['price']]);

                return $existing;
            }

            return $range->prices()->create($data);
        });
    }

    public function deleteRangePrice(CarrierRangePrice $price): void
    {
        $price->delete();
    }
}
