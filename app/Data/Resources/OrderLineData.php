<?php

namespace App\Data\Resources;

use Lunar\Models\OrderLine;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class OrderLineData extends Data
{
    public function __construct(
        public int $id,
        public Lazy|OrderData $order,
        public Lazy|ProductVariantData $productVariant,
        public string $type,
        public string $description,
        public ?string $option,
        public string $identifier,
        public FormattedPriceData $unitPrice,
        public int $unitQuantity,
        public int $quantity,
        public FormattedPriceData $subTotal,
        public FormattedPriceData $discountTotal,
        public FormattedPriceData $taxTotal,
        public FormattedPriceData $total,
        public ?string $notes,
        public ?array $meta,
    ) {
    }

    public static function fromModel(OrderLine $orderLine): self
    {
        return new self(
            $orderLine->getKey(),
            Lazy::whenLoaded('order', $orderLine, fn () => OrderData::from($orderLine->order)),
            Lazy::whenLoaded('purchasable', $orderLine, fn () => ProductVariantData::from($orderLine->purchasable)->include('product')),
            $orderLine->type,
            $orderLine->description,
            $orderLine->option,
            $orderLine->identifier,
            FormattedPriceData::from($orderLine->unit_price),
            $orderLine->unit_quantity,
            $orderLine->quantity,
            FormattedPriceData::from($orderLine->sub_total),
            FormattedPriceData::from($orderLine->discount_total),
            FormattedPriceData::from($orderLine->tax_total),
            FormattedPriceData::from($orderLine->total),
            $orderLine->notes,
            $orderLine->meta ? (array) $orderLine->meta : null
        );
    }
}
