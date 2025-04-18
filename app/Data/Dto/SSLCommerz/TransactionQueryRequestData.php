<?php

namespace App\Data\Dto\SSLCommerz;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;

class TransactionQueryRequestData extends Data
{
    public function __construct(
        #[MapName('sessionkey')]
        public string $sessionKey,
    ) {
    }
}
