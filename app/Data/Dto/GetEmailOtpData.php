<?php

namespace App\Data\Dto;

use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Data;

class GetEmailOtpData extends Data
{
    public function __construct(
        #[Email]
        public string $email
    ) {
    }
}
