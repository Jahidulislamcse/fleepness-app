<?php

namespace App\Listeners;

use Illuminate\Queue\Events\JobExceptionOccurred;

class ListenJobExceptionOccurred
{
    /**
     * Create the event listener.
     */
    public function __construct() {}

    /**
     * Handle the event.
     */
    public function handle(JobExceptionOccurred $event): void
    {
        activity('job')->withProperties([
            'job' => [
                'id' => $event->job->getJobId(),
                'uuid' => $event->job->uuid(),
                'queue' => $event->job->getQueue(),
                'name' => $event->job->getName(),
                'connection_name' => $event->job->getConnectionName(),
            ],
            'connection_name' => $event->connectionName,
            'exception' => [
                'message' => $event->exception->getMessage(),
                'trace' => $event->exception->getTraceAsString(),
            ],
        ])->log('Job exception occured');
    }
}
