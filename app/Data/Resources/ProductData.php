<?php

namespace App\Data\Resources;

use App\Constants\ProductStatuses;
use App\Models\Product;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class ProductData extends Data
{
    public function __construct(
        public int $id,
        public Lazy|ProductTypeData $productType,
        public array $attributeData,
        public ProductStatuses $status,
        public Lazy|VendorData $vendor,
        public ?BrandData $brand,
        #[DataCollectionOf(PriceData::class)]
        public Lazy|DataCollection $prices,
        #[DataCollectionOf(ProductVariantData::class)]
        public Lazy|DataCollection $variants
    ) {
    }

    public static function fromModel(Product $product): self
    {

        return new self(
            $product->getKey(),
            Lazy::whenLoaded('productType', $product, fn () => ProductTypeData::from($product->productType)),
            $product->attribute_data->toArray(),
            $product->status,
            Lazy::whenLoaded('vendor', $product, fn () => VendorData::from($product->vendor)),
            $product->brand ? BrandData::from($product->brand) : null,
            Lazy::whenLoaded('prices', $product, fn () => PriceData::collection($product->prices)),
            Lazy::whenLoaded('variants', $product, fn () => ProductVariantData::collection($product->variants))
        );
    }
}
