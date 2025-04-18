<?php

namespace App\Data\Dto;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Attributes\Validation\ArrayType;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class MakeOrderPickupData extends Data
{
    public function __construct(
        #[ArrayType('id', 'name')]
        public readonly array $recipientCity,
        #[ArrayType('id', 'name')]
        public readonly array $recipientZone,
        #[ArrayType('id', 'name')]
        public readonly ?array $recipientArea,
    ) {
    }
}
