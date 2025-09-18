<?php

namespace App\Http\Controllers\LiveStreaming;

use App\Constants\GateNames;
use App\Constants\LivestreamStatuses;
use App\Data\Dto\GeneratePublisherTokenData;
use App\Facades\Livestream as LivestreamService;
use App\Models\Livestream;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;

class GetLivestreamPublisherTokenController extends Controller
{
    use AuthorizesRequests;

    /**
     * Handle the incoming request.
     */
    public function __invoke($id)
    {
        $livestream = Livestream::findOrFail($id);
        $this->authorize(GateNames::GET_LIVESTREAM_PUBLISHER_TOKEN->value, $livestream);

        $userId = auth('sanctum')->id() ?? Str::random(6);
        $displayName = auth('sanctum')->user()?->name ?? Str::random(6);
        $roomName = $livestream->getRoomName();

        $data = new GeneratePublisherTokenData(
            roomName: $roomName,
            identity: $userId,
            displayName: $displayName,
            metadata: [
                'livestream_identity' => $livestream->getKey(),
                ...(auth('sanctum')->check() ? auth('sanctum')->user()->toArray() : []),
            ]
        );

        $roomToken = LivestreamService::generatePublisherToken($data);
        $livestream->startRecording();
        $livestream->save();
        $livestream->setStatus(LivestreamStatuses::STARTED->value);
        
        return response()->json([
            'token' => $roomToken,
        ]);
    }
}
