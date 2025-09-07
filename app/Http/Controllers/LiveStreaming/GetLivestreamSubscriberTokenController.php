<?php

namespace App\Http\Controllers\LiveStreaming;

use Illuminate\Routing\Controller;

use App\Constants\GateNames;
use App\Data\Dto\GenerateSubscriberTokenData;
use App\Data\Resources\UserData;
use App\Facades\Livestream as FacadesLivestream;
use App\Models\Livestream;
use Illuminate\Support\Str;

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
        $userId = auth("sanctum")->id() ?? Str::random(6);
        $displayName = auth("sanctum")->user()?->name ?? Str::random(6);

        $roomName = $livestream->getRoomName();

        $data = new GenerateSubscriberTokenData(
            roomName: $roomName,
            identity: $userId,
            displayName: $displayName,
            isPublic: auth()->guest(),
            metadata: auth("sanctum")->check() ? auth("sanctum")->user()->toArray() : []

        );

        $roomToken = FacadesLivestream::generateSubscriberToken($data);

        return response()->json([
            'token' => $roomToken,
        ]);
    }
}
