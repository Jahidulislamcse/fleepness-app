<?php

namespace App\Data\Dto\SSLCommerz;

use Carbon\Carbon;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class TransactionQueryResponseData extends Data
{
    public function __construct(
        public string $status,
        #[MapName('sessionkey')]
        public string $sessionKey,
        public Carbon $tranDate,
        public string $tranId,
        public string $valId,
        public float $amount,
        public float $storeAmount,
        public string $cardType,
        public string $cardNo,
        public string $currency,
        public string $bankTranId,
        public string $cardIssuer,
        public string $cardBrand,
        public string $cardIssuerCountry,
        public string $cardIssuerCountryCode,
        public string $currencyType,
        public float $currencyAmount,
        public string $valueA,
        public string $valueB,
        public string $valueC,
        public string $valueD,
        public int $riskLevel,
        public string $riskTitle
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
