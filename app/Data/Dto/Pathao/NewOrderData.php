<?php

namespace App\Data\Dto\Pathao;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class NewOrderData extends Data
{
    public function __construct(
        public int $consignmentId,
        public string $merchantOrderId,
        public string $orderStatus,
        public int $deliveryFee,
    ) {
    }
}
