<?php

namespace App\Enums;

enum DeliveryStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case PickedUp = 'picked_up';
    case OnTheWay = 'on_the_way';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Confirmed => 'Confirmed',
            self::PickedUp => 'Picked Up',
            self::OnTheWay => 'On The Way',
            self::Delivered => 'Delivered',
            self::Cancelled => 'Cancelled',
        };
    }

    /**
     * The only legal next states. Exactly the six values the reference panel
     * filters by; an order moves forward, or is cancelled before it ships.
     *
     * @return list<self>
     */
    public function next(): array
    {
        return match ($this) {
            self::Pending => [self::Confirmed, self::Cancelled],
            self::Confirmed => [self::PickedUp, self::Cancelled],
            self::PickedUp => [self::OnTheWay],
            self::OnTheWay => [self::Delivered],
            self::Delivered, self::Cancelled => [],
        };
    }

    public function canBecome(self $to): bool
    {
        return in_array($to, $this->next(), true);
    }

    public function isFinal(): bool
    {
        return $this->next() === [];
    }
}
