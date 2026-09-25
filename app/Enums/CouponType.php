<?php

namespace App\Enums;

enum CouponType: string
{
    case ProductBase = 'product_base';
    case CartBase = 'cart_base';
    case WelcomeBase = 'welcome_base';

    public function label(): string
    {
        return match ($this) {
            self::ProductBase => 'Product Base',
            self::CartBase => 'Cart Base',
            self::WelcomeBase => 'Welcome Base',
        };
    }
}
