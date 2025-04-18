<?php

namespace App\Data\Dto;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class UpdateCartProductData extends Data
{
    public function __construct(
        #[DataCollectionOf(UpdateCartProductItemData::class)]
        public DataCollection $cartlines
    ) {
    }
}
