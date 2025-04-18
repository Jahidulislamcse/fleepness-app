<?php

namespace App\Data\Dto;

use AhmedAliraqi\LaravelMediaUploader\Entities\TemporaryFile;
use App\Models\User;
use Carbon\Carbon;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Attributes\Validation\AfterOrEqual;
use Spatie\LaravelData\Attributes\Validation\Exists;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Spatie\LaravelData\Optional;

#[MapName(SnakeCaseMapper::class)]
class CreateLivestremData extends Data
{
    public function __construct(
        public string $title,
        #[Exists(User::class, 'id')]
        public int $vendorId,
        // #[Exists(TemporaryFile::class, 'token')]
        // public string $thumbnailPicture,
        #[AfterOrEqual('today')]
        public Carbon|Optional|null $scheduledTime
    ) {}
}
