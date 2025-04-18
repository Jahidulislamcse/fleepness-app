<?php

namespace App\Data\Dto;

use App\Models\Product;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class AddCartProductData extends Data
{
    public function __construct(
        public ?int $cartId,
        public array $products
    ) {
    }

    public static function rules(): array
    {
        return [
            'products.*.quantity' => ['numeric'],
            'products.*.id' => ['required', Rule::exists(Product::class, 'id')],
        ];
    }
}
