<?php

namespace App\Data\Resources;

use Lunar\Models\Cart;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class CartData extends Data
{
    public function __construct(
        public int $id,
        public Lazy|UserData $user,
        #[DataCollectionOf(CartLineData::class)]
        public DataCollection $lines,
        public ?FormattedPriceData $total,
        public ?FormattedPriceData $subTotal,
        public ?FormattedPriceData $subTotalDiscounted,
        public ?FormattedPriceData $shippingTotal,
        public ?FormattedPriceData $taxTotal,
        public ?FormattedPriceData $discountTotal,
        public ?FormattedPriceData $shippingSubTotal,

    ) {
    }

    public static function fromModel(Cart $cart): self
    {
        return new self(
            $cart->getKey(),
            Lazy::whenLoaded('user', $cart, fn () => UserData::from($cart->user)),
            CartLineData::collection($cart->lines),
            $cart->total ? FormattedPriceData::from($cart->total) : null,
            $cart->subTotal ? FormattedPriceData::from($cart->subTotal) : null,
            $cart->subTotalDiscounted ? FormattedPriceData::from($cart->subTotalDiscounted) : null,
            $cart->shippingTotal ? FormattedPriceData::from($cart->shippingTotal) : null,
            $cart->taxTotal ? FormattedPriceData::from($cart->taxTotal) : null,
            $cart->discountTotal ? FormattedPriceData::from($cart->discountTotal) : null,
            $cart->shippingSubTotal ? FormattedPriceData::from($cart->shippingSubTotal) : null,
        );
    }
}
