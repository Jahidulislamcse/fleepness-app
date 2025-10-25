<?php

namespace App\Support\Broadcaster;

use Illuminate\Support\Str;
use UnexpectedValueException;
use App\Events\FcmBroadcastFailed;
use Illuminate\Support\Collection;
use Kreait\Firebase\Messaging\SendReport;
use Kreait\Firebase\Messaging\CloudMessage;
use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Support\HigherOrderWhenProxy;
use Kreait\Laravel\Firebase\Facades\Firebase;
use Illuminate\Broadcasting\BroadcastException;
use Kreait\Firebase\Messaging\MulticastSendReport;
use Illuminate\Broadcasting\Broadcasters\Broadcaster;
use Illuminate\Broadcasting\Broadcasters\UsePusherChannelConventions;
use App\Support\Notification\Contracts\FcmBroadcastNotifiableByDevice;

class FcmBroadcaster extends Broadcaster
{
    use UsePusherChannelConventions;

    public function auth($request)
    {
        $request->validate([
            'device_token' => ['required', 'string'],
            'channel_name' => ['required', 'string'],
        ]);

        $channelName = $this->normalizeChannelName($request->channel_name);

        return parent::verifyUserCanAccessChannel($request, $channelName);
    }

    public function unauth($request)
    {
        $request->validate([
            'device_token' => ['required', 'string'],
            'channel_name' => ['required', 'string'],
        ]);

        $channelName = $this->normalizeChannelName($request->channel_name);

        $result = Firebase::messaging()->unsubscribeFromTopic($channelName, $request->device_token);

        return response(['success' => true, 'result' => $result]);
    }

    public function validAuthenticationResponse($request, $result)
    {
        $channelName = $this->normalizeChannelName($request->channel_name);

        $result = Firebase::messaging()->subscribeToTopic($channelName, $request->device_token);

        return response(['success' => true, 'result' => $result]);
    }

    /**
     * @param  \Illuminate\Support\Collection<int,string>  $channels
     * @return \Illuminate\Support\Collection<int,string>
     */
    private function normalizeChannelCollection(Collection $channels)
    {
        return $channels->map($this->normalizeChannelName(...))
            ->map(fn ($channel) => Str::snake($channel))
            ->unique();
    }

    /**
     * Handle the report for the notification and dispatch any failed notifications.
     */
    protected function checkReportForFailures(mixed $notifiable, string $event, MulticastSendReport $report): MulticastSendReport
    {
        collect($report->getItems())
            ->filter(fn (SendReport $report) => $report->isFailure())
            ->each(fn (SendReport $report) => $this->dispatchFailedNotification($notifiable, $event, $report));

        return $report;
    }

    /**
     * Dispatch failed event.
     */
    protected function dispatchFailedNotification(mixed $notifiable, string $event, SendReport $report): void
    {
        event(new FcmBroadcastFailed($notifiable, $event, self::class, [
            'report' => $report,
        ]));
    }

    public function broadcast(array $channels, $event, array $payload = [])
    {
        data_forget($payload, 'socket');
        $notifiable = data_get($payload, 'notifiable');

        if ($notifiable) {
            throw_unless(
                $notifiable instanceof FcmBroadcastNotifiableByDevice,
                UnexpectedValueException::class,
                'notifiable must implement '.FcmBroadcastNotifiableByDevice::class
            );
            data_forget($payload, 'notifiable');
        }

        $notification = data_get($payload, 'notification');
        if ($notification) {
            data_forget($payload, 'notification');
        }

        $payload['event'] = $event;

        foreach ($payload as $key => $value) {
            if (! is_string($value)) {
                $payload[$key] = Json::encode($value);
            } else {
                $payload[$key] = (string) $value;
            }
        }

        [$privateChannels, $publicChannels] = collect($this->formatChannels($channels))
            ->partition(fn ($channel) => $this->isGuardedChannel($channel));

        $publicChannels = $this->normalizeChannelCollection($publicChannels);
        $privateChannels = $this->normalizeChannelCollection($privateChannels);
        /** @var FcmBroadcastNotifiableByDevice|null $notifiable */
        if ($notifiable) {
            $this->handlePrivateChannels($notifiable, $event, $payload, $notification);
        }

        $this->handlePublicChannels($publicChannels, $payload, $notification);
    }

    private function handlePrivateChannels(FcmBroadcastNotifiableByDevice $notifiable, string $event, array $payload, ?array $notification = null)
    {
        if (! empty($notifiable->routeBroadcastNotificationForFcmTokens())) {

            try {
                /** @var HigherOrderWhenProxy|CloudMessage $message */
                $message = (new HigherOrderWhenProxy(CloudMessage::new()))
                    ->condition(! is_null($notification));

                /** @var CloudMessage $message */
                $message = $message
                    ->withNotification($notification)
                    ->withData($payload);

                $report = Firebase::messaging()->sendMulticast(
                    $message,
                    $notifiable->routeBroadcastNotificationForFcmTokens()
                );
                $this->checkReportForFailures($notifiable, $event, $report);
            } catch (\Throwable $th) {
                throw new BroadcastException(
                    $th->getMessage(),
                    $th->getCode(),
                    $th
                );
            }
        }
    }

    /**
     * @param  Collection<int,string>  $channels
     * @return void
     */
    private function handlePublicChannels(Collection $channels, array $payload, ?array $notification = null)
    {
        try {
            $channels
                ->chunk(100)
                ->map(function ($chunk) use ($payload, $notification) {
                    return $chunk->map(function ($channel) use ($payload, $notification) {

                        /** @var HigherOrderWhenProxy|CloudMessage $message */
                        /** @var HigherOrderWhenProxy|CloudMessage $message */
                        $message = (new HigherOrderWhenProxy(CloudMessage::new()))
                            ->condition(! is_null($notification));

                        /** @var CloudMessage $message */
                        $message = $message
                            ->withNotification($notification)
                            ->withData($payload)
                            ->toTopic($channel);

                        return $message;
                    });
                })
                ->each(function ($messages) {
                    Firebase::messaging()->sendAll($messages->toArray());
                });
        } catch (\Throwable $th) {
            throw new BroadcastException(
                $th->getMessage(),
                $th->getCode(),
                $th
            );
        }
    }
}
