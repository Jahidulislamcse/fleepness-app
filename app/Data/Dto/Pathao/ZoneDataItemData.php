<?php

namespace App\Data\Dto\Pathao;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class ZoneDataItemData extends Data
{
    public function __construct(
        public readonly int $zoneId,
        public readonly string $zoneName,
    ) {
    }
}
