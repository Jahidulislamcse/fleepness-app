<?php

namespace App\Data\Dto;

use AhmedAliraqi\LaravelMediaUploader\Entities\TemporaryFile;
use App\Constants\GateNames;
use App\Models\Product;
use App\Models\Vendor;
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
use Spatie\LaravelData\Support\Validation\ValidationContext;

#[MapName(SnakeCaseMapper::class)]
class CreateVendorProductData extends Data
{
    public function __construct(
        public string $sku,
        public array $attributeData,
        public float $price,
        #[Exists(ProductType::class, 'id')]
        public int $productTypeId,
        #[Exists(TemporaryFile::class, 'token')]
        public string $thumbnailPicture,
        #[Exists(TemporaryFile::class, 'token')]
        public string $pictures
    ) {
    }

    public static function rules(ValidationContext $context)
    {
        $productAttrsCollection = Attribute::system(Product::class)
            ->get()
            ->pluck('name')
            ->pluck('en');

        /** @var Collection */
        $productAttrRules = $productAttrsCollection
            ->mapWithKeys(fn (string $attrName) => ["attribute_data.{$attrName}" => ['required']]);

        $productTypeId = Arr::get($context->payload, 'productTypeId');
        if ($productTypeId) {
            /** @var ProductType */
            $productType = ProductType::find($productTypeId);
            $productTypeAttrsCollection = $productType->mappedAttributes->pluck('name')->pluck('en');

            $productTypeAttrRules = $productTypeAttrsCollection
                ->mapWithKeys(fn (string $attrName) => ["attribute_data.{$attrName}" => ['required']]);

            $productAttrRules = $productAttrRules->merge($productTypeAttrRules);
        }

        $rules = $productAttrRules->toArray();

        return $rules;
    }

    public static function authorize(): Response|bool
    {
        /** @var Vendor */
        $vendor = request()->route('vendor');

        return Gate::authorize(GateNames::CREATE_VENDOR_PRODUCT->value, $vendor);

    }
}
