<?php

namespace App\Http\Controllers\LiveStreaming;

use App\Models\User;
use App\Models\Livestream;
use Illuminate\Support\Str;
use App\Constants\GateNames;
use Illuminate\Routing\Controller;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Container\Attributes\Auth;
use App\Data\Dto\GenerateSubscriberTokenData;
use App\Facades\Livestream as FacadesLivestream;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class GetLivestreamSubscriberTokenController extends Controller
{
    use AuthorizesRequests;

    /**
     * Handle the incoming request.
     */
    public function __invoke(Livestream $livestream, #[CurrentUser] ?User $buyer, #[Auth] Guard $guard)
    {
        $this->authorize(GateNames::GET_LIVESTREAM_SUBSCRIBER_TOKEN->value, $livestream);
        $userId = $buyer?->getKey() ?? Str::random(6);
        $displayName = $buyer->name ?? Str::random(6);

        $roomName = $livestream->getRoomName();

        $data = new GenerateSubscriberTokenData(
            roomName: $roomName,
            identity: $userId,
            displayName: $displayName,
            isPublic: $guard->guest(),
            metadata: [
                'livestream_identity' => $livestream->getKey(),
                ...($buyer?->toArray() ?? []),
            ]

        );

        $roomToken = FacadesLivestream::generateSubscriberToken($data);

        return response()->json([
            'token' => $roomToken,
        ]);
    }
}
