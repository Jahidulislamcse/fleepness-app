<?php

namespace App\Enums;

enum SellerOrderStatus: string
{
    case Pending = 'pending';
    case Packaging = 'packaging';
    case On_The_Way = 'on_the_way';
    case Delivered = 'delivered';
    case Delayed = 'delayed';
}
