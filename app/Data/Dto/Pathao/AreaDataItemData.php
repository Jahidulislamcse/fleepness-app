<?php

namespace App\Data\Dto\Pathao;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class AreaDataItemData extends Data
{
    public function __construct(
        public readonly int $areaId,
        public readonly string $areaName,
        public readonly bool $homeDeliveryAvailable,
        public readonly bool $pickupAvailable,
    ) {
    }
}
