<?php

namespace App\Constants;

enum LivestreamStatuses: string
{
    case INITIAL = 'initial';
    case STARTED = 'started';
    case FINISHED = 'finished';
}
