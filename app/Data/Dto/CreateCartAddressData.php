<?php

namespace App\Data\Dto;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class CreateCartAddressData extends Data
{
    public function __construct(
        public readonly ?string $title,
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly ?string $companyName,
        public readonly string $lineOne,
        public readonly ?string $lineTwo,
        public readonly ?string $lineThree,
        public readonly string $city,
        public readonly ?string $state,
        public readonly string $postcode,
        public readonly ?string $deliveryInstructions,
        public readonly string $contactEmail,
        public readonly string $contactPhone,
    ) {
    }
}
