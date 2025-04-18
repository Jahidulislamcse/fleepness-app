<?php

namespace App\Data\Resources;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaDetailsData extends Data
{
    public function __construct(
        public Lazy|int $width,
        public Lazy|int $height,
        public Lazy|int $ratio,
        public Lazy|int $duration,
    ) {
    }

    public static function fromModel(Media $media): self
    {
        return new self(
            Lazy::create(fn () => $media->getCustomProperty('width')),
            Lazy::create(fn () => $media->getCustomProperty('height')),
            Lazy::create(fn () => $media->getCustomProperty('ratio')),
            Lazy::create(fn () => $media->getCustomProperty('duration')),
        );
    }
}
