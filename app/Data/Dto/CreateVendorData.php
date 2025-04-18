<?php

namespace App\Data\Dto;

use App\Models\User;
use Spatie\LaravelData\Attributes\MapName;
// use Spatie\LaravelData\Attributes\Validation\ArrayType;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class CreateVendorData extends Data
{
    public function __construct(
        #[Max(200)]
        public string $name,
        #[Max(160000)]
        public string $description,
        #[Max(200)]
        public string $address,
        #[Email]
        public string $contactEmail,
        public string $contactPhone,
        // #[ArrayType('id', 'name')]
        // public array $city,
        // #[ArrayType('id', 'name')]
        // public array $area,
        // #[ArrayType('id', 'name')]
        // public array $zone,
    ) {}

    public static function authorize(): bool
    {
        /** @var User $user */
        $user = auth()->user();

        $user->loadCount('vendors');

        return $user->vendors_count === 0;
    }
}
