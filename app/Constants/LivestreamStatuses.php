<?php

namespace App\Constants;

enum LivestreamStatuses: string
{
    case INITIAL = 'initial';
    case STARTED = 'started';
    case FINISHED = 'finished';

    public function isFinished(): bool
    {
        return LivestreamStatuses::FINISHED === $this;
    }

    public function isStarted(): bool
    {
        return LivestreamStatuses::STARTED === $this;
    }

    public function isInitial(): bool
    {
        return LivestreamStatuses::INITIAL === $this;
    }
}
