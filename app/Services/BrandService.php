<?php

namespace App\Services;

use App\Models\Brand;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/** Every write to a brand, for the panel and the API alike. Input is validated already (BrandRequest). */
class BrandService
{
    public function create(array $data): Brand
    {
        return DB::transaction(function () use ($data) {
            $brand = new Brand($data);
            $brand->forceFill(['slug' => Brand::uniqueSlug($data['name'])])->save();

            return $brand;
        });
    }

    /** The slug survives a rename, so links already shared keep working. */
    public function update(Brand $brand, array $data): Brand
    {
        return DB::transaction(fn () => tap($brand)->update($data));
    }

    /** Sets exactly the given flags, so repeating a request changes nothing. */
    public function setFlags(Brand $brand, array $flags): Brand
    {
        return DB::transaction(fn () => tap($brand)->update($flags));
    }

    /**
     * products.brand_id is nullOnDelete, so deleting a brand in use would
     * silently strip it from those products. Refuse instead.
     *
     * @throws DomainException while any product uses the brand
     */
    public function delete(Brand $brand): void
    {
        DB::transaction(function () use ($brand) {
            $count = $brand->products()->count();

            if ($count > 0) {
                throw new DomainException(sprintf(
                    '%d %s this brand; move them to another brand first.',
                    $count, $count === 1 ? 'product uses' : Str::plural('product', $count).' use',
                ));
            }

            $brand->delete();
        });
    }
}
