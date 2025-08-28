<?php

namespace App\Data\Dto;

use App\Constants\GateNames;
use App\Models\Product;
use Illuminate\Support\Facades\Gate;
use Illuminate\Auth\Access\Response;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class RemoveLivestreamProductData extends Data
{
    public function __construct(
        public array $productIds
    ) {
    }

    public static function rules(): array
    {
        return [
            'product_ids.*' => [Rule::exists(Product::class, 'id')],
        ];
    }

    
}
