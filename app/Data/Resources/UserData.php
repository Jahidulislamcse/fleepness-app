<?php

namespace App\Data\Resources;

use App\Constants\GenderTypes;
use App\Models\User;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class UserData extends Data
{
    public function __construct(
        public int $id,
        public ?string $email,
        public ?string $name,
        public ?GenderTypes $gender,
        public ?string $address,
        public ?string $phoneNumber,
        public ?MediaData $profilePicture,
        #[DataCollectionOf(VendorData::class)]
        public DataCollection $vendors
    ) {
    }

    public static function fromModel(User $user): self
    {
        return new self(
            $user->getKey(),
            $user->email,
            $user->name,
            $user->gender,
            $user->address,
            $user->phone_number,
            $user->getFirstMedia('avatar') ? MediaData::from($user->getFirstMedia('avatar')) : null,
            VendorData::collection($user->vendors)
        );
    }
}
