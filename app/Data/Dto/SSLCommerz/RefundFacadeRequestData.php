<?php

namespace App\Data\Dto\SSLCommerz;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class RefundFacadeRequestData extends Data
{
    public function __construct(
        public readonly string $sessionKey,
        public readonly string $merchantTransId,
        public readonly float $merchantTransAmount,
        public readonly string $merchantTransCurrency,
        public readonly float $refundAmount,
        public readonly string $refundRemarks,
        public readonly ?string $refeId
    ) {
    }
}
