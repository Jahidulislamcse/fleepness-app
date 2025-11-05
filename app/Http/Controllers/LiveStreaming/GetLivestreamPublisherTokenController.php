<?php

namespace App\Http\Controllers\LiveStreaming;

use App\Models\User;
use App\Models\Livestream;
use App\Constants\GateNames;
use Illuminate\Routing\Controller;
use App\Constants\LivestreamStatuses;
use App\Data\Dto\GeneratePublisherTokenData;
use App\Facades\Livestream as LivestreamService;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class GetLivestreamPublisherTokenController extends Controller
{
    use AuthorizesRequests;

    /**
     * Handle the incoming request.
     */
    public function __invoke(Livestream $livestream, #[CurrentUser] User $user)
    {
        $this->authorize(GateNames::GET_LIVESTREAM_PUBLISHER_TOKEN->value, $livestream);

        $userId = $user->getKey();
        $displayName = $user->name;
        $roomName = $livestream->room_name;

        $data = new GeneratePublisherTokenData(
            roomName: $roomName,
            identity: $userId,
            displayName: $displayName,
            metadata: [
                'livestream_identity' => $livestream->getKey(),
                $user->toArray(),
            ]
        );

        $roomToken = LivestreamService::generatePublisherToken($data);
        $livestream->startRecording();
        $livestream->status = LivestreamStatuses::STARTED;
        $livestream->save();

        return response()->json([
            'token' => $roomToken,
        ]);
    }
}
