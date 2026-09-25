<?php

namespace App\Enums;

enum StockVisibility: string
{
    case Quantity = 'quantity';
    case Text = 'text';
    case Hide = 'hide';

    public function label(): string
    {
        return match ($this) {
            self::Quantity => 'Quantity',
            self::Text => 'Text',
            self::Hide => 'Hide',
        };
    }
}
