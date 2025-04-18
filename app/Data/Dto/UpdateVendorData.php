<?php

namespace App\Data\Dto;

use AhmedAliraqi\LaravelMediaUploader\Entities\TemporaryFile;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Exists;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Spatie\LaravelData\Optional;

#[MapName(SnakeCaseMapper::class)]
class UpdateVendorData extends Data
{
    public function __construct(
        public string|Optional $name,
        public string|Optional $address,
        public string|Optional $description,
        #[Email]
        public string|Optional $contactEmail,
        public string|Optional $contactPhone,
        #[Exists(TemporaryFile::class, 'token')]
        public string|Optional $profilePicture,
        #[Exists(TemporaryFile::class, 'token')]
        public string|Optional $coverPicture,
    ) {
    }

    public static function authorize(): bool
    {
        return true;
    }
}
