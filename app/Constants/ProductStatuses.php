<?php

namespace App\Constants;

enum ProductStatuses: string
{
    case PUBLISHED = 'published';
    case ARCHIVED = 'archived';
}
