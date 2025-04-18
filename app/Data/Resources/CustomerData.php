<?php

namespace App\Data\Resources;

use Carbon\Carbon;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class CustomerData extends Data
{
    public function __construct(
        public int $id,
        public ?string $title,
        public string $firstName,
        public string $lastName,
        public ?string $companyName,
        public ?string $vatNo,
        public ?array $meta,
        public ?array $attributeData,
        public Carbon $createdAt,
        public ?Carbon $updatedAt,
    ) {
    }
}
