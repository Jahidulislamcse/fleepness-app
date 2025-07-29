<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FirebaseService;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Laravel\Firebase\Facades\Firebase;

class FirebaseController extends Controller
{

    public function testFirebase(Request $request)
    {
        try {
            $deviceToken = 'dOaNG4V7BzYCaamjEu_mMC:APA91bF-FcDhaM9M6VR8Ps4UdQiLPN3w7Lrp5kkUzcrrlUbcBG5eTBP9uOnHCwZnsYl54uScLpormPqhBQreyg68rlMRSQpAlQAI8HWoLPwU7s_ir3gTA_o';

            $title = 'My Notification Title';

            $body = 'My Notification Body';
            $imageUrl = 'https://picsum.photos/400/200';

            $notification = Notification::fromArray([
                'title' => $title,
                'body' => $body,
                'image' => $imageUrl,
            ]);

            $data = [
                'title' => $title,
                'body' => $body,
            ];

            $message = CloudMessage::new()
                ->withNotification($notification) // optional
                //  ->withData($data) // optional
                ->toToken($deviceToken);

            Firebase::messaging()->send($message);

            return response()->json(['message' => 'Message sent successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
