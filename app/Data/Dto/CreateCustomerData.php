<?php

namespace App\Data\Dto;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class CreateCustomerData extends Data
{
    public function __construct(
        public ?string $title,
        public string $firstName,
        public string $lastName,
        public ?string $companyName,
    ) {
    }
}
