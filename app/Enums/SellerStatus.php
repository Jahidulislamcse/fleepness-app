<?php

namespace App\Enums;

enum SellerStatus: string
{
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function messageBody()
    {
        return match ($this) {
            self::Approved => 'Your seller request has been approved by Fleepness!',
            self::Rejected => 'Your seller request has been rejected. ❌',
        };
    }

    public function messageTitle()
    {
        return match ($this) {
            self::Approved => 'Congratulations',
            self::Rejected => 'We\'re Sorry',
        };
    }
}
