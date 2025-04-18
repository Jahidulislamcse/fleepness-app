<?php

namespace App\Data\Resources;

use Spatie\LaravelData\Data;

class BrandData extends Data
{
    public function __construct(
        public int $id,
        public string $name
    ) {
    }
}
