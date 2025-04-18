<?php

namespace App\Data\Resources;

use App\Models\Vendor;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class VendorData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public string $address,
        public string $contactEmail,
        public string $contactPhone,
        public ?MediaData $profilePicture,
        public ?MediaData $coverPicture,
        public string $description,
        public Lazy|int $products_count,
        public Lazy|int $followers_count,
        public Lazy|UserData $user,
    ) {
    }

    public static function fromModel(Vendor $vendor): self
    {
        return new self(
            $vendor->getKey(),
            $vendor->name,
            $vendor->address,
            $vendor->contact_email,
            $vendor->contact_phone,
            $vendor->getFirstMedia('profile') ? MediaData::from($vendor->getFirstMedia('profile')) : null,
            $vendor->getFirstMedia('cover') ? MediaData::from($vendor->getFirstMedia('cover')) : null,
            $vendor->description,
            Lazy::create(fn () => $vendor->products_count),
            Lazy::create(fn () => $vendor->followers_count),
            Lazy::whenLoaded('user', $vendor, fn () => UserData::from($vendor->user)),
        );
    }
}
