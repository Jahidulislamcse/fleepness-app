<?php

namespace App\Data\Resources;

use Lunar\Models\ProductType;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class ProductTypeData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        #[DataCollectionOf(AttributeData::class)]
        public Lazy|DataCollection $attributeData,
    ) {
    }

    public static function fromModel(ProductType $productType): self
    {
        return new self(
            $productType->getKey(),
            $productType->name,
            Lazy::whenLoaded('mappedAttributes', $productType, fn () => AttributeData::collection($productType->mappedAttributes))
        );
    }
}
