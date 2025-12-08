<?php

namespace App\Webhooks\Livekit;

use App\Models\Livestream;
use App\Constants\LivestreamStatuses;
use Agence104\LiveKit\WebhookReceiver;
use Illuminate\Database\Eloquent\Casts\Json;
use Spatie\WebhookClient\Jobs\ProcessWebhookJob;

class LivekitProcessWebhookJob extends ProcessWebhookJob
{
    public function handle(): void
    {
        $webhookPayload = $this->webhookCall->payload;

        $receiver = new WebhookReceiver(config('services.livekit.api_key'), config('services.livekit.api_secret'));
        $event = $receiver->receive(data_get($webhookPayload, 'raw_data', ''), null, true);

        $eventName = $event->getEvent();

        switch ($eventName) {
            case 'room_started':
                logger()->info('room started', [$event->getRoom()->getMetadata()]);
                break;
            case 'room_finished':
                logger()->info('room finished', [$event->getRoom()->getMetadata()]);

                if ($event->hasRoom()) {
                    $roomMetadata = Json::decode($event->getRoom()->getMetadata());
                    $livestreamId = data_get($roomMetadata, 'livestream_identity');
                    $livestream = \App\Models\Livestream::query()->find($livestreamId);
                    if ($livestream) {
                        $livestream->ended_at = now();
                        $livestream->status = LivestreamStatuses::FINISHED;
                        $livestream->save();
                    }
                }
                break;
            case 'participant_joined':
                if ($event->hasRoom() && $event->hasParticipant()) {
                    $roomMetadata = Json::decode($event->getRoom()->getMetadata());
                    $livestreamId = data_get($roomMetadata, 'livestream_identity');
                    $livestream = \App\Models\Livestream::query()->find($livestreamId);

                    if ($event->getParticipant()->getPermission()->getCanPublish()) {
                        return;
                    }

                    $participantUserId = $event->getParticipant()->getIdentity();
                    $user = \App\Models\User::query()->find($participantUserId);

                    if ($livestream) {
                        if ($user) {
                            $changes = $livestream->participants()->syncWithoutDetaching([$user->getKey()]);
                            logger()->info('Participant joined', [$changes]);

                            if (! empty($changes['attached'])) {
                                $livestream->increment('total_participants');
                            }
                        } else {
                            /** @var list<string> */
                            $joinedParticipantUsers = cache()->get($livestream->getRoomName(), []);
                            if (! in_array($participantUserId, $joinedParticipantUsers)) {
                                $joinedParticipantUsers[] = $participantUserId;
                                cache()->put($livestream->getRoomName(), $joinedParticipantUsers, now()->addHour());
                                $livestream->increment('total_participants');
                            }
                        }
                    }
                }

                break;
            case 'participant_left':
                $event->getParticipant()->getIdentity();
                break;
            case 'track_published':
            case 'track_unpublished':
            case 'egress_updated':
            case 'ingress_started':
            case 'ingress_ended':
                break;
            case 'egress_started':
                if ($event->hasEgressInfo()) {
                    // $roomMetadata = Json::decode($event->getRoom()->getMetadata());
                    // $livestreamId = data_get($roomMetadata, 'livestream_identity');
                    // $livestream = Livestream::find($livestreamId);

                    // $eventEgressId = $event->getEgressInfo()->getEgressId();

                    // if ($livestream && $livestream->egress_id !== $eventEgressId) {
                    //     $livestream->update([
                    //         'egress_id' => $eventEgressId,
                    //     ]);
                    // }
                }
                break;
            case 'egress_ended':
                logger()->info('egress ended', [$event->hasEgressInfo(), $event->hasRoom()]);
                if ($event->hasEgressInfo()) {
                    $eventEgressId = $event->getEgressInfo()->getEgressId();
                    $livestream = \App\Models\Livestream::query()->firstWhere([
                        'egress_id' => $eventEgressId,
                    ]);

                    if ($livestream) {
                        $recodings = \App\Facades\Livestream::getRecordingsFor($livestream);

                        $thumbnails = \App\Facades\Livestream::getThumbnailsFor($livestream);

                        $shortVideos = \App\Facades\Livestream::getShortVideosFor($livestream);

                        $livestream->update([
                            'egress_data' => [
                                'recordings' => $recodings,
                                'thumbnails' => $thumbnails,
                                'short_videos' => $shortVideos,
                            ],
                        ]);
                    }
                }
                break;
        }
    }
}
