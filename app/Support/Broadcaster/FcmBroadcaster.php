<?php

namespace App\Support\Broadcaster;

use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Kreait\Firebase\Messaging\CloudMessage;
use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Support\HigherOrderWhenProxy;
use Kreait\Laravel\Firebase\Facades\Firebase;
use Illuminate\Broadcasting\BroadcastException;
use Illuminate\Broadcasting\Broadcasters\Broadcaster;
use Illuminate\Broadcasting\Broadcasters\UsePusherChannelConventions;

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

    public function broadcast(array $channels, $event, array $payload = [])
    {
        data_forget($payload, 'socket');
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

        /** @var Collection<int,string> */
        $channels = collect($this->formatChannels($channels))
            ->map($this->normalizeChannelName(...))
            ->map(Str::snake(...))
            ->unique();

        try {
            $channels
                ->chunk(100)
                ->map(function ($chunk) use ($payload, $notification) {
                    return $chunk->map(function ($channel) use ($payload, $notification) {

                        /** @var HigherOrderWhenProxy|CloudMessage $message */
                        $message = (new HigherOrderWhenProxy(CloudMessage::new()))
                            ->condition($notification);

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
