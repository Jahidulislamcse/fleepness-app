<?php

namespace App\Data\Dto;

use Lunar\Models\CartLine;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Attributes\Validation\Exists;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class UpdateCartProductItemData extends Data
{
    public function __construct(
        #[Exists(CartLine::class, 'id')]
        public int $id,
        public int $quantity
    ) {
    }
}
