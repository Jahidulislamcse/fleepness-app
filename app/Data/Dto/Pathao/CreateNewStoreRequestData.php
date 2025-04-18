<?php

namespace App\Data\Dto\Pathao;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Spatie\LaravelData\Optional;

#[MapName(SnakeCaseMapper::class)]
class CreateNewStoreRequestData extends Data
{
    public function __construct(
        public readonly string $name,
        public readonly string $contactName,
        public readonly string $contactNumber,
        public readonly Optional|string|null $secondaryContact,
        public readonly string $address,
        public readonly int $cityId,
        public readonly int $zoneId,
        public readonly int $areaId,
    ) {
    }
}
