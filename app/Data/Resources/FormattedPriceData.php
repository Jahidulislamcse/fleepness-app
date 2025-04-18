<?php

namespace App\Data\Resources;

use Lunar\DataTypes\Price;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class FormattedPriceData extends Data
{
    public function __construct(
        public float $decimal,
        public float $unitDecimal,
        public mixed $formatted,
        public mixed $unitFormatted,
    ) {
    }

    public static function fromModel(Price $price): self
    {
        return new self(
            $price->decimal(),
            $price->unitDecimal(),
            $price->formatted(),
            $price->unitFormatted()
        );
    }
}
