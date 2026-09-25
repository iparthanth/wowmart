<?php

namespace App\Enums;

enum ShippingType: string
{
    case Free = 'free';
    case FlatRate = 'flat_rate';

    public function label(): string
    {
        return match ($this) {
            self::Free => 'Free',
            self::FlatRate => 'Flat Rate',
        };
    }
}
