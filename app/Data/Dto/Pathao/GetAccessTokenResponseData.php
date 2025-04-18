<?php

namespace App\Data\Dto\Pathao;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class GetAccessTokenResponseData extends Data
{
    public function __construct(
        public string $accessToken,
        public string $refreshToken,
        public string $tokenType,
        public null|int|string $expiresIn,
    ) {
    }
}
