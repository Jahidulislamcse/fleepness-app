<?php

namespace App\Listeners;

use App\Listeners\Traits\LogsHttpClient;
use Illuminate\Http\Client\Events\RequestSending;

class ActivityLogForHttpClientRequestSending
{
    use LogsHttpClient;

    /**
     * Handle the event.
     */
    public function handle(RequestSending $event): void
    {
        activity(self::ACTIVITY_LOG_NAME)
            ->withProperties([
                'method' => $event->request->method(),
                'uri' => $event->request->url(),
                'headers' => $this->headers($event->request->headers()),
                'payload' => $this->payload($this->input($event->request)),
            ])
            ->log('request sending');
    }
}
