<?php

namespace App\Data\Dto\Pathao;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class CreateNewStoreResponseData extends Data
{
    public function __construct(
        public readonly string $message,
        public readonly string $type,
        public readonly int $code,
        public readonly NewStoreData $data,
    ) {
    }
}
