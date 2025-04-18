<?php

namespace App\Data\Dto\SSLCommerz;

use Spatie\LaravelData\Data;

class InitiatePaymentResponseData extends Data
{
    public function __construct(
        public string $status,
        public string $failedreason,
        public string $sessionkey,
        public array $gw,
        public string $GatewayPageURL,
        public string $storeBanner,
        public string $storeLogo,
        public array $desc
    ) {
    }

    public function isValid()
    {
        return $this->status === 'SUCCESS';
    }
}
