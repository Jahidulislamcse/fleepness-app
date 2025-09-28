<?php

namespace App\Listeners;

use App\Listeners\Traits\LogsHttpClient;
use Illuminate\Http\Client\Events\ResponseReceived;

class ActivityLogForHttpClientResponseReceived
{
    use LogsHttpClient;

    /**
     * Handle the event.
     */
    public function handle(ResponseReceived $event): void
    {
        activity(self::ACTIVITY_LOG_NAME)
            ->withProperties([
                'method' => $event->request->method(),
                'uri' => $event->request->url(),
                'headers' => $this->headers($event->request->headers()),
                'payload' => $this->payload($this->input($event->request)),
                'response_status' => $event->response->status(),
                'response_headers' => $this->headers($event->response->headers()),
                'response' => $this->response($event->response),
                'duration' => $this->duration($event->response),
            ])
            ->log('response received');
    }
}
