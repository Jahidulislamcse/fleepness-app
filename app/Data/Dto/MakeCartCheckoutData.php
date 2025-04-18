<?php

namespace App\Data\Dto;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class MakeCartCheckoutData extends Data
{
    public function __construct(
        public CreateCustomerData $customer,
        public CreateCartAddressData $shippingAddress,
        public CreateCartAddressData $billingAddress,
    ) {
    }
}
