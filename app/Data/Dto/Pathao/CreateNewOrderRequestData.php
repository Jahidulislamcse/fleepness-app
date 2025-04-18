<?php

namespace App\Data\Dto\Pathao;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class CreateNewOrderRequestData extends Data
{
    public function __construct(
        public readonly string $storeId,
        public readonly ?string $merchantOrderId,
        public readonly string $senderName,
        public readonly string $senderPhone,
        public readonly string $recipientName,
        public readonly string $recipientPhone,
        public readonly string $recipientAddress,
        public readonly int $recipientCity,
        public readonly int $recipientZone,
        public readonly ?string $recipientArea,
        public readonly int $deliveryType,
        public readonly int $itemType,
        public readonly ?string $specialInstruction,
        public readonly int $itemQuantity,
        public readonly float $itemWeight,
        public readonly float $amountToCollect,
        public readonly ?string $itemDescription,
    ) {
    }
}
