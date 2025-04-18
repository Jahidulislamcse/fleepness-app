<?php

namespace App\Data\Dto\SSLCommerz;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class OrderValidationResponseData extends Data
{
    public function __construct(
        public string $status,
        public string $tranDate,
        public string $tranId,
        public string $valId,
        public null|float|string $amount,
        public null|float|string $storeAmount,
        public ?string $cardType,
        public ?string $cardNo,
        public ?string $currency,
        public ?string $bankTranId,
        public ?string $cardIssuer,
        public ?string $cardBrand,
        public ?string $cardIssuerCountry,
        public ?string $cardIssuerCountryCode,
        public ?string $currencyType,
        public null|float|string $currencyAmount,
        public ?string $valueA,
        public ?string $valueB,
        public ?string $valueC,
        public ?string $valueD,
        public null|int|string $riskLevel,
        public ?string $riskTitle
    ) {
    }

    public function isValid()
    {
        return $this->status === 'VALID' || $this->status === 'VALIDATED';
    }

    public function checkData(
        string $merchantTransId,
        float $merchantTransAmount,
        string $merchantTransCurrency,
    ): bool {
        if (! $this->isValid()) {
            return false;
        }

        if ($merchantTransCurrency === 'BDT') {
            return trim($merchantTransId) === trim($this->tranId) && (abs($merchantTransAmount - $this->amount) < 1) && trim($merchantTransCurrency) === trim('BDT');
        }

        return trim($merchantTransId) === trim($this->tranId) && (abs($merchantTransAmount - $this->currencyAmount) < 1) && trim($merchantTransCurrency) === trim($this->currencyType);
    }
}
