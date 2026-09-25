<?php

namespace App\Enums;

enum UserType: string
{
    case Customer = 'customer';
    case Seller = 'seller';
    case Admin = 'admin';
    case Staff = 'staff';
    case DeliveryBoy = 'delivery_boy';

    public function label(): string
    {
        return match ($this) {
            self::Customer => 'Customer',
            self::Seller => 'Seller',
            self::Admin => 'Admin',
            self::Staff => 'Staff',
            self::DeliveryBoy => 'Delivery Boy',
        };
    }
}
