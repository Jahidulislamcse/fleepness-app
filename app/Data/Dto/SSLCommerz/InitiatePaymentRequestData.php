<?php

namespace App\Data\Dto\SSLCommerz;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class InitiatePaymentRequestData extends Data
{
    public function __construct(
        public float $totalAmount,
        public string $currency,
        public string $tranId,

        public string $cusName,
        public string $cusEmail,
        public string $cusAdd1,
        public ?string $cusAdd2,
        public string $cusCity,
        public ?string $cusState,
        public string $cusPostcode,
        public string $cusCountry,
        public string $cusPhone,
        public string $cusFax,

        public string $shipName,
        public string $shipAdd1,
        public ?string $shipAdd2,
        public string $shipCity,
        public ?string $shipState,
        public string $shipPostcode,
        public string $shipPhone,
        public string $shipCountry,
        public string $shippingMethod,

        public string $productName,
        public string $productCategory,
        public string $productProfile,

        public string $valueA,
        public string $valueB,
        public string $valueC,
        public string $valueD,
    ) {
    }
}
