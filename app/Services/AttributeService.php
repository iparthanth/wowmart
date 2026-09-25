<?php

namespace App\Services;

use App\Models\Attribute;
use App\Models\AttributeValue;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/** Every write to an attribute or its values. Input is validated already (AttributeRequest, AttributeValueRequest). */
class AttributeService
{
    public function create(array $data): Attribute
    {
        return DB::transaction(fn () => Attribute::create($data));
    }

    public function update(Attribute $attribute, array $data): Attribute
    {
        return DB::transaction(fn () => tap($attribute)->update($data));
    }

    /**
     * Values go with the attribute (cascade), which is expected. Products offering
     * it as a variant choice would be left pointing at an id that no longer exists.
     *
     * @throws DomainException while any product offers the attribute
     */
    public function delete(Attribute $attribute): void
    {
        DB::transaction(function () use ($attribute) {
            $count = $attribute->productsUsing()->count();

            if ($count > 0) {
                throw new DomainException(sprintf(
                    '%d %s this attribute for variants; remove it from %s first.',
                    $count, $count === 1 ? 'product offers' : Str::plural('product', $count).' offer', $count === 1 ? 'that product' : 'those products',
                ));
            }

            $attribute->delete();
        });
    }

    public function addValue(Attribute $attribute, array $data): AttributeValue
    {
        return DB::transaction(fn () => $attribute->values()->create($data));
    }

    public function updateValue(AttributeValue $value, array $data): AttributeValue
    {
        return DB::transaction(fn () => tap($value)->update($data));
    }

    // ponytail: no usage guard - products.choice_options keeps the value's text, not its id,
    // so an existing variant survives the value's removal. Guard here if that ever changes.
    public function deleteValue(AttributeValue $value): void
    {
        DB::transaction(fn () => $value->delete());
    }
}
