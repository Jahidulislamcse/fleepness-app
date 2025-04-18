<?php

namespace App\Data\Dto;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class GeneratePublisherTokenData extends Data
{
    public function __construct(
        public string $roomName,
        public string $identity,
        public string $displayName,
    ) {
    }
}
