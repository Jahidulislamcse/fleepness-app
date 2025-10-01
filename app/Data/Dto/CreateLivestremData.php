<?php

namespace App\Data\Dto;

use App\Models\User;
use App\Models\Product;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Contracts\Database\Query\Builder;
use Spatie\LaravelData\Attributes\Validation\Exists;

#[MapName(SnakeCaseMapper::class)]
class CreateLivestremData extends Data
{
    /**
     * @param  list<int>  $products
     */
    public function __construct(
        public readonly string $title,
        public readonly array $products,
    ) {}

    public static function rules(#[CurrentUser] User $user): array
    {
        return [
            'products' => ['required', 'array'],
            'products.*' => ['required', new Exists(Product::class, 'id', where: function (Builder $query) use ($user) {
                $query->where('user_id', $user->getKey());
            })],
        ];
    }
}
