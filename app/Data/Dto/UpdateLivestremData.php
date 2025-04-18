<?php

namespace App\Data\Dto;

use Carbon\Carbon;
use App\Constants\LivestreamStatuses;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Attributes\Validation\AfterOrEqual;
use Spatie\LaravelData\Attributes\Validation\Enum;
use Spatie\LaravelData\Attributes\Validation\Exists;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Spatie\LaravelData\Optional;
use App\Models\User; // Assuming the media will be associated with a User model
use Illuminate\Http\UploadedFile;

#[MapName(SnakeCaseMapper::class)]
class UpdateLivestreamData extends Data
{
    public function __construct(
        public Optional|string $title,
        #[AfterOrEqual('today')]
        public Optional|Carbon|null $scheduledTime,
        public Optional|UploadedFile $thumbnailPicture, // Now using UploadedFile for media handling
        #[Enum(LivestreamStatuses::class)]
        public LivestreamStatuses|Optional $status,
    ) {}

    /**
     * Handle the file upload and associate it with a model.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function handleMedia(User $user): void
    {
        if ($this->thumbnailPicture instanceof UploadedFile) {
            // Upload the file using Spatie Media Library
            $user->addMedia($this->thumbnailPicture)
                ->toMediaCollection('thumbnail_pictures');
        }
    }
}
