<?php

namespace App\Data;

use AhmedAliraqi\LaravelMediaUploader\Entities\TemporaryFile;
use App\Constants\GenderTypes;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Attributes\Validation\Exists;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Spatie\LaravelData\Optional;

#[MapName(SnakeCaseMapper::class)]
class UpdateProfileData extends Data
{
    public function __construct(
        public string|Optional $name,
        #[Exists(TemporaryFile::class, 'token')]
        public string|Optional $profilePicture,
        public GenderTypes|Optional $gender,
        public string|Optional $address,
        public string|Optional $phonenumber,
    ) {}
}
