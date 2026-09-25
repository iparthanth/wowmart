<?php

namespace App\Enums;

enum DiscountType: string
{
    case Amount = 'amount';
    case Percent = 'percent';

    public function label(): string
    {
        return match ($this) {
            self::Amount => 'Amount',
            self::Percent => 'Percent',
        };
    }
}
