<?php

namespace App\Data;

use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Data;

class UpdateEmailData extends Data
{
    public function __construct(
        public string $otp,
        #[Email]
        public string $email,
    ) {
    }
}
