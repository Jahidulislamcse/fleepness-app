<?php

namespace App\Data\Dto;

use AhmedAliraqi\LaravelMediaUploader\Entities\TemporaryFile;
use App\Constants\GateNames;
use App\Models\Product;
use Arr;
use Gate;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Collection;
use Lunar\Models\Attribute;
use Lunar\Models\ProductType;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Attributes\Validation\Exists;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\Support\Validation\ValidationContext;

#[MapName(SnakeCaseMapper::class)]
class UpdateVendorProductData extends Data
{
    public function __construct(
        public Optional|string $sku,
        public Optional|array $attributeData,
        public Optional|int $price,
        #[Exists(ProductType::class, 'id')]
        public Optional|int $productTypeId,
        #[Exists(TemporaryFile::class, 'token')]
        public Optional|string $thumbnailPicture,
        #[Exists(TemporaryFile::class, 'token')]
        public Optional|string $pictures
    ) {

    }

    public static function authorize(): Response|bool
    {
        $vendor = request()->route('vendor');
        $product = request()->route('product');

        return Gate::authorize(GateNames::UPDATE_VENDOR_PRODUCT->value, [$vendor, $product]);
    }

    public static function rules(ValidationContext $context)
    {
        $rulesCollection = collect();
        /** @var Product */
        $product = request()->route('product');

        if (Arr::has($context->payload, 'attributeData')) {
            $productAttrsCollection = Attribute::system(Product::class)
                ->get()
                ->pluck('name')
                ->pluck('en');

            /** @var Collection */
            $productAttrRules = $productAttrsCollection
                ->mapWithKeys(fn (string $attrName) => ["attribute_data.{$attrName}" => ['required']]);

            $rulesCollection = $rulesCollection->merge($productAttrRules);
        }

        $productTypeId = Arr::get($context->payload, 'productTypeId');
        if ($productTypeId && $product->product_type_id !== $productTypeId) {
            /** @var ProductType */
            $productType = ProductType::find($productTypeId);
            $productTypeAttrsCollection = $productType->mappedAttributes->pluck('name')->pluck('en');

            $productTypeAttrRules = $productTypeAttrsCollection
                ->mapWithKeys(fn (string $attrName) => ["attribute_data.{$attrName}" => ['required']]);

            $rulesCollection = $rulesCollection->merge($productTypeAttrRules);
        }

        $rules = $rulesCollection->toArray();

        return $rules;
    }
}
