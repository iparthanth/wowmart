<?php

namespace App\Services;

use App\Models\PickupPoint;
use Illuminate\Support\Facades\DB;

/** Every write to a pickup point. Input is validated already (PickupPointRequest). */
class PickupPointService
{
    public function create(array $data): PickupPoint
    {
        return DB::transaction(fn () => PickupPoint::create($data));
    }

    public function update(PickupPoint $point, array $data): PickupPoint
    {
        return DB::transaction(fn () => tap($point)->update($data));
    }

    /** A pickup point can always be deleted: nothing else references it yet. */
    public function delete(PickupPoint $point): void
    {
        $point->delete();
    }
}
