<?php

namespace App\Data\Dto\Pathao;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class GetZonesByCityResponseData extends Data
{
    public function __construct(
        public string $message,
        public string $type,
        public int $code,
        public ZoneDataWrapperData $data,
    ) {
    }
}
