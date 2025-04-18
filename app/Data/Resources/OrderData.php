<?php

namespace App\Data\Resources;

use ArrayObject;
use Carbon\Carbon;
use Lunar\Models\Order;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class OrderData extends Data
{
    public function __construct(
        public int $id,
        public null|Lazy|UserData $user,
        public null|Lazy|CustomerData $customer,
        public string $status,
        public string $reference,
        public ?string $customerReference,
        public FormattedPriceData $subTotal,
        public FormattedPriceData $discountTotal,
        public FormattedPriceData $shippingTotal,
        public FormattedPriceData $taxTotal,
        public FormattedPriceData $total,
        public ?string $notes,
        public string $currencyCode,
        public ?string $compareCurrencyCode,
        public float $exchangeRate,
        public ?Carbon $placedAt,
        public ?ArrayObject $meta,
        #[DataCollectionOf(OrderLineData::class)]
        public DataCollection $lines,
    ) {
    }

    public static function fromModel(Order $order): self
    {
        return new self(
            $order->getKey(),
            Lazy::whenLoaded('user', $order, fn () => UserData::from($order->user)),
            Lazy::whenLoaded('customer', $order, fn () => CustomerData::from($order->customer)),
            $order->status,
            $order->reference,
            $order->customer_reference,
            FormattedPriceData::from($order->sub_total),
            FormattedPriceData::from($order->discount_total),
            FormattedPriceData::from($order->shipping_total), /** @phpstan-ignore-line */
            FormattedPriceData::from($order->tax_total),
            FormattedPriceData::from($order->total),
            $order->notes,
            $order->currency_code,
            $order->compare_currency_code,
            $order->exchange_rate,
            $order->placed_at,
            $order->meta, /** @phpstan-ignore-line */
            OrderLineData::collection($order->lines)
        );
    }
}
