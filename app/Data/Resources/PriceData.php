<?php

namespace App\Data\Resources;

use Lunar\Models\Price;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class PriceData extends Data
{
    public function __construct(
        public int $id,
        public ?CurrencyData $currency,
        public FormattedPriceData $price,
        public ?FormattedPriceData $comparePrice,
        public int $tier,
    ) {
    }

    public static function fromModel(Price $price): self
    {

        /** @var \Lunar\DataTypes\Price */
        $priceDataType = $price->price;
        /** @var \Lunar\DataTypes\Price|null */
        $comparePriceDataType = $price->compare_price;

        return new self(
            $price->getKey(),
            $price->currency ? CurrencyData::from($price->currency) : null,
            FormattedPriceData::from($priceDataType),
            $comparePriceDataType ? FormattedPriceData::from($comparePriceDataType) : null,
            $price->tier
        );
    }
}
