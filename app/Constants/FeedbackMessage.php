<?php

namespace App\Constants;

enum FeedbackMessage: string
{
    case OTP_SENT = 'OTP Sent';
    case OTP_MISMATCH = 'OTP mismatch';
    case EMAIL_VERIFIED = 'Email Verified';
    case LOGIN_SUCCESS = 'Login successful';
    case IMGPROXY_SOURCE_URL_MISSING = 'Missing source_url query parameter';
    case IMGPROXY_PROCESSING_OPTIONS_MISSING = 'Missing processing_options query parameter';
}
