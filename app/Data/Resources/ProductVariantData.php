<?php

namespace App\Data\Resources;

use Lunar\Models\ProductVariant;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class ProductVariantData extends Data
{
    public function __construct(
        public int $id,
        public ?string $taxRef,
        public int $unitQuantity,
        public ?string $sku,
        public bool $shippable,
        public int $stock,
        public int $backorder,
        public string $purchasable,
        public ?string $lengthUnit,
        public ?string $widthUnit,
        public ?string $heightUnit,
        public ?string $weightUnit,
        public ?string $volumeUnit,
        public ?float $lengthValue,
        public ?float $widthValue,
        public ?float $heightValue,
        public ?float $weightValue,
        public ?float $volumeValue,
        public Lazy|ProductData $product,
        #[DataCollectionOf(PriceData::class)]
        public Lazy|DataCollection $prices,

    ) {
    }

    public static function fromModel(ProductVariant $productVariant): self
    {
        return new self(
            $productVariant->getKey(),
            $productVariant->tax_ref,
            $productVariant->unit_quantity,
            $productVariant->sku,
            $productVariant->isShippable(),
            $productVariant->stock,
            $productVariant->backorder,
            $productVariant->purchasable,
            $productVariant->length_unit,
            $productVariant->width_unit,
            $productVariant->height_unit,
            $productVariant->weight_unit,
            $productVariant->volume_unit,
            $productVariant->length_value,
            $productVariant->width_value,
            $productVariant->height_value,
            $productVariant->weight_value,
            $productVariant->volume_value,
            Lazy::whenLoaded('product', $productVariant, fn () => ProductData::from($productVariant->product)),
            Lazy::whenLoaded('prices', $productVariant, fn () => PriceData::collection($productVariant->prices)),
        );
    }
}
