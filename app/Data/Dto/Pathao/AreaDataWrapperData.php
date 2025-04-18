<?php

namespace App\Data\Dto\Pathao;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class AreaDataWrapperData extends Data
{
    public function __construct(
        #[DataCollectionOf(AreaDataItemData::class)]
        public DataCollection $data,
    ) {
    }
}
