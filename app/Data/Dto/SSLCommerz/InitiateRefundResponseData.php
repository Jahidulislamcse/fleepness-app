<?php

namespace App\Data\Dto\SSLCommerz;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class InitiateRefundResponseData extends Data
{
    public function __construct(
        public string $bankTranId,
        public string $transId,
        public string $refundRefId,
        public string $status,
        #[MapName('errorReason')]
        public string $errorReason
    ) {
    }

    public function isValid(): bool
    {
        return $this->status === 'success';
    }
}
