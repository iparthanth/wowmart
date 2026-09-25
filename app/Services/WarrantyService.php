<?php

namespace App\Services;

use App\Models\Warranty;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/** Every write to a warranty. Input is validated already (WarrantyRequest). */
class WarrantyService
{
    public function create(array $data): Warranty
    {
        return DB::transaction(fn () => Warranty::create($data));
    }

    public function update(Warranty $warranty, array $data): Warranty
    {
        return DB::transaction(fn () => tap($warranty)->update($data));
    }

    /**
     * products.warranty_id is nullOnDelete, so deleting a warranty in use would
     * silently strip it from those products. Refuse instead.
     *
     * @throws DomainException while any product uses the warranty
     */
    public function delete(Warranty $warranty): void
    {
        DB::transaction(function () use ($warranty) {
            $count = $warranty->products()->count();

            if ($count > 0) {
                throw new DomainException(sprintf(
                    '%d %s this warranty; pick another warranty on %s first.',
                    $count, $count === 1 ? 'product uses' : Str::plural('product', $count).' use', $count === 1 ? 'it' : 'them',
                ));
            }

            $warranty->delete();
        });
    }
}
