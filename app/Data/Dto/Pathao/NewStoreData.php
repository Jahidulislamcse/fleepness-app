<?php

namespace App\Data\Dto\Pathao;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class NewStoreData extends Data
{
    public function __construct(
        public readonly int $storeId,
        public readonly string $storeName
    ) {

    }
}
