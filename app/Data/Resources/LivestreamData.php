<?php

namespace App\Data\Resources;

use App\Models\Livestream;
use Carbon\Carbon;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class LivestreamData extends Data
{
    public function __construct(
        public int $id,
        public VendorData $vendor,
        public string $title,
        public ?MediaData $thumbnailPicture,
        public string $status,
        #[DataCollectionOf(ProductData::class)]
        public DataCollection $products,
        public ?Carbon $scheduledTime,
        public ?int $totalDuration,
        public ?Carbon $startedAt,
        public ?Carbon $endedAt
    ) {
    }

    public static function fromMode(Livestream $livestream): self
    {
        $thumbnail = $livestream->getFirstMedia('thumbnail');

        return new self(
            $livestream->getKey(),
            VendorData::from($livestream->vendor),
            $livestream->title,
            isset($thumbnail) ? MediaData::from($thumbnail) : null,
            $livestream->status,
            DataCollection::from($livestream->products->map(fn($product) => ProductData::from($product))),
            $livestream->scheduled_time ? Carbon::parse($livestream->scheduled_time) : null,
            $livestream->total_duration,
            $livestream->started_at,
            $livestream->ended_at
        );
    }
}
