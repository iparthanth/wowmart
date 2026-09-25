<?php

namespace App\Services;

use App\Models\Color;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/** Every write to a colour. Input is validated already (ColorRequest), code uppercase. */
class ColorService
{
    public function create(array $data): Color
    {
        return DB::transaction(fn () => Color::create($data));
    }

    /**
     * Products store the code, not the colour's id, so a changed code leaves them
     * on the old one. Refuse that while products use it.
     *
     * @throws DomainException when the code changes while products use the colour
     */
    public function update(Color $color, array $data): Color
    {
        return DB::transaction(function () use ($color, $data) {
            if ($data['code'] !== strtoupper($color->code) && ($count = $color->productsUsing()->count()) > 0) {
                throw new DomainException(sprintf(
                    '%s this colour, and they keep the old code. Rename it instead, or add a new colour for the new code.',
                    $this->productCount($count),
                ));
            }

            return tap($color)->update($data);
        });
    }

    /** @throws DomainException while any product offers the colour */
    public function delete(Color $color): void
    {
        DB::transaction(function () use ($color) {
            $count = $color->productsUsing()->count();

            if ($count > 0) {
                throw new DomainException(sprintf('%s this colour; remove it from them first.', $this->productCount($count)));
            }

            $color->delete();
        });
    }

    private function productCount(int $count): string
    {
        return $count === 1 ? '1 product offers' : $count.' '.Str::plural('product', $count).' offer';
    }
}
