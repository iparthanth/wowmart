<?php

namespace App\Services;

use App\Models\ProductTax;
use App\Models\Tax;
use DomainException;
use Illuminate\Support\Facades\DB;

/** Every write to taxes and to a product's tax assignment. Input is validated already. */
class TaxService
{
    public function create(array $data): Tax
    {
        return DB::transaction(fn () => Tax::create($data));
    }

    public function update(Tax $tax, array $data): Tax
    {
        return DB::transaction(fn () => tap($tax)->update($data));
    }

    /** @throws DomainException while any product is assigned the tax */
    public function delete(Tax $tax): void
    {
        DB::transaction(function () use ($tax) {
            $count = $tax->productTaxes()->count();

            if ($count > 0) {
                throw new DomainException(sprintf(
                    '%s assigned this tax; remove those assignments first.',
                    $count === 1 ? '1 product is' : $count.' products are',
                ));
            }

            $tax->delete();
        });
    }

    /** @throws DomainException when the product is already charged under this tax */
    public function assign(array $data): ProductTax
    {
        return DB::transaction(function () use ($data) {
            $this->assertSingleAssignment(null, (int) $data['product_id'], (int) $data['tax_id']);

            return ProductTax::create($data);
        });
    }

    /** @throws DomainException when the product is already charged under this tax */
    public function updateAssignment(ProductTax $productTax, array $data): ProductTax
    {
        return DB::transaction(function () use ($productTax, $data) {
            $this->assertSingleAssignment($productTax, (int) $productTax->product_id, (int) $data['tax_id']);

            return tap($productTax)->update($data);
        });
    }

    /** Removing an assignment never cascades anywhere, so there is no guard. */
    public function unassign(ProductTax $productTax): void
    {
        $productTax->delete();
    }

    /** @throws DomainException when a tax with this name already exists for this product */
    public function assertSingleAssignment(?ProductTax $ignore, int $productId, int $taxId): void
    {
        $taken = ProductTax::where('product_id', $productId)->where('tax_id', $taxId)
            ->when($ignore, fn ($q) => $q->whereKeyNot($ignore->id))
            ->exists();

        if ($taken) {
            throw new DomainException(sprintf(
                'This product is already charged under %s.',
                Tax::find($taxId)?->name ?? 'this tax',
            ));
        }
    }
}
