<?php

namespace App\Data\Resources;

use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Spatie\MediaLibrary\Conversions\Conversion;
use Spatie\MediaLibrary\Conversions\ConversionCollection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

#[MapOutputName(SnakeCaseMapper::class)]
class MediaData extends Data
{
    public function __construct(
        public int $id,
        public string $url,
        public string $preview,
        public string $name,
        public string $fileName,
        public string $type,
        public string $mimeType,
        public int $size,
        public string $humanReadableSize,
        public MediaDetailsData $details,
        /**
         * @var array<string, string>
         */
        public array $conversions,
    ) {
        $this->preview = $this->getPreviewUrl();
    }

    /**
     * Get the preview url.
     *
     * @return string|void
     */
    public function getPreviewUrl()
    {
        if ($this->type === 'image') {
            return $this->url;
        }

        return 'https://cdn.jsdelivr.net/npm/laravel-file-uploader/dist/img/attach.png';
    }

    public static function fromModel(Media $media): self
    {
        $details = MediaDetailsData::from($media);

        if ($media->type === 'video') {
            $details = $details->include('duration');
        }

        if ($media->type === 'image') {
            $details = $details->include('width', 'height', 'ratio');
        }

        $conversions = [];

        foreach (array_keys($media->getGeneratedConversions()->toArray()) as $conversionName) {
            $conversion = ConversionCollection::createForMedia($media)
                ->first(fn (Conversion $conversion) => $conversion->getName() === $conversionName);

            if ($conversion) {
                $conversions[$conversionName] = $media->getFullUrl($conversionName);
            }
        }

        return new self(
            $media->getKey(),
            $media->getFullUrl(),
            $media->preview_url,
            $media->name,
            $media->file_name,
            $media->type,
            $media->mime_type,
            $media->size,
            $media->humanReadableSize,
            $details,
            $conversions,
        );
    }
}
