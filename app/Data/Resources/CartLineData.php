<?php

namespace App\Data\Resources;

use Lunar\Models\CartLine;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class CartLineData extends Data
{
    public function __construct(
        public int $id,
        public ?FormattedPriceData $unitPrice,
        public ?FormattedPriceData $total,
        public ?FormattedPriceData $subTotal,
        public ?FormattedPriceData $subTotalDiscounted,
        public ?FormattedPriceData $taxAmount,
        public ?FormattedPriceData $discountTotal,
        public int $quantity,
        public ProductVariantData $productVariant,
    ) {
    }

    public static function fromModel(CartLine $cartLine): self
    {
        return new self(
            $cartLine->getKey(),
            $cartLine->unitPrice ? FormattedPriceData::from($cartLine->unitPrice) : null,
            $cartLine->total ? FormattedPriceData::from($cartLine->total) : null,
            $cartLine->subTotal ? FormattedPriceData::from($cartLine->subTotal) : null,
            $cartLine->subTotalDiscounted ? FormattedPriceData::from($cartLine->subTotalDiscounted) : null,
            $cartLine->taxAmount ? FormattedPriceData::from($cartLine->taxAmount) : null,
            $cartLine->discountTotal ? FormattedPriceData::from($cartLine->discountTotal) : null,
            $cartLine->quantity,
            ProductVariantData::from($cartLine->purchasable)->include('product')
        );
    }
}
