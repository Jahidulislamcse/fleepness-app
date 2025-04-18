<?php

namespace App\Data\Resources;

use Lunar\Models\Currency;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class CurrencyData extends Data
{
    public function __construct(
        public int $id,
        public string $code,
        public string $name,
        public float $exchangeRate,
        public int $decimalPlaces,
        public bool $enabled,
        public bool $default,
    ) {
    }

    public static function fromModel(Currency $currency): self
    {
        return new self(
            $currency->getKey(),
            $currency->code, $currency->name,
            $currency->exchange_rate,
            $currency->decimal_places,
            $currency->enabled,
            $currency->default
        );
    }
}
