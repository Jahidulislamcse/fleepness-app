<?php

namespace App\Listeners;

use App\Listeners\Traits\LogsHttpClient;
use Illuminate\Http\Client\Events\ConnectionFailed;

class ActivityLogForHttpClientConnectionFailed
{
    use LogsHttpClient;

    /**
     * Handle the event.
     */
    public function handle(ConnectionFailed $event): void
    {
        activity(self::ACTIVITY_LOG_NAME)
            ->withProperties([
                'method' => $event->request->method(),
                'uri' => $event->request->url(),
                'headers' => $this->headers($event->request->headers()),
                'payload' => $this->input($event->request),
            ])
            ->log('connection failed');
    }
}
