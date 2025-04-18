<?php

namespace App\Data\Dto\SSLCommerz;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class InitiateRefundRequestData extends Data
{
    public function __construct(
        public string $bankTranId,
        public float $refundAmount,
        public string $refundRemarks,
        public ?string $refeId
    ) {
    }
}
