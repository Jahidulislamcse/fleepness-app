<?php

namespace App\Data\Dto;

use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Data;

class VerifyEmailOtpData extends Data
{
    public function __construct(
        public string $otp,
        #[Email]
        public string $email
    ) {
    }
}
