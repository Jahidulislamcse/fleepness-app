<?php

namespace App\Data\Dto;

use App\Data\Resources\UserData;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Spatie\LaravelData\Optional;

#[MapName(SnakeCaseMapper::class)]
class GenerateSubscriberTokenData extends Data
{
    public function __construct(
        public string $roomName,
        public string $identity,
        public string $displayName,
        public bool $isPublic,
        public Optional|UserData|null $userData
    ) {
    }
}
