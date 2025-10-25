<?php

namespace App\Enums;

enum SellerOrderStatus: string
{
    case Pending = 'pending';
    case Packaging = 'packaging';
    case On_The_Way = 'on_the_way';
    case Delivered = 'delivered';
    case Delayed = 'delayed';
    case Rejected = 'rejected';
    case Active = 'active';

    public function isDelivered(): bool
    {
        return self::Delivered === $this;
    }

    public function isActive(): bool
    {
        return self::Active === $this;
    }

    public function isRejected(): bool
    {
        return self::Rejected === $this;
    }
}
