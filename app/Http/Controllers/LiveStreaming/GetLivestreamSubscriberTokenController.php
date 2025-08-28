<?php

namespace App\Http\Controllers\LiveStreaming;

use Illuminate\Routing\Controller;

use App\Constants\GateNames;
use App\Data\Dto\GenerateSubscriberTokenData;
use App\Data\Resources\UserData;
use App\Facades\Livestream as FacadesLivestream;
use App\Models\Livestream;

class GetLivestreamSubscriberTokenController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke($livestreamId)
    {
        $livestream = Livestream::find($livestreamId);
        // $livestream->load('vendor');
        // $this->authorize(GateNames::GET_LIVESTREAM_SUBSCRIBER_TOKEN->value, $livestream);
        $userId = auth("sanctum")->id() ?? 'public';
        $displayName = auth("sanctum")->user()?->name ?? 'public';

        $roomName = $livestream->getRoomName();

        $data = new GenerateSubscriberTokenData(
            roomName: $roomName,
            identity: $userId,
            displayName: $displayName,
            isPublic: $userId === 'public',
            metadata: auth("sanctum")->check() ? auth("sanctum")->user()->toArray() : []

        );

        $roomToken = FacadesLivestream::generateSubscriberToken($data);

        return response()->json([
            'token' => $roomToken,
        ]);
    }
}
