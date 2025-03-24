<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;

class SMSController extends Controller
{
    public function sendSMS(Request $request)
    {
        // Validate the incoming request data
        $validated = $request->validate([
            'Message' => 'required|string',
            'MobileNumbers' => 'required|string',  // Comma-separated mobile numbers
            'ScheduleTime' => 'nullable|date_format:Y-m-d H:i',
        ]);

        // Fetch API credentials from .env
        $apiKey = env('SMS_API_KEY');
        $clientId = env('SMS_CLIENT_ID');
        $senderId = env('SMS_SENDER_ID');
        $apiUrl = env('SMS_API_URL') . '/SendSMS'; // Append endpoint

        // Prepare the parameters for the API request
        $params = [
            'ApiKey' => $apiKey,
            'ClientId' => $clientId,
            'SenderId' => $senderId,
            'Message' => $validated['Message'],
            'MobileNumbers' => $validated['MobileNumbers'],
            'Is_Unicode' => true,
            'Is_Flash' => false,
            'DataCoding' => '8',
            'ScheduleTime' => $validated['ScheduleTime'] ?? null,
            'GroupId' => '',
        ];

        // Send the request to the SMS API
        $response = Http::get($apiUrl, $params);

        // Check for a successful response
        if ($response->successful()) {
            return response()->json([
                'ErrorCode' => 0,
                'ErrorDescription' => 'Success',
                'Data' => $response->json()['Data'] ?? [],
            ]);
        } else {
            return response()->json([
                'ErrorCode' => $response->status(),
                'ErrorDescription' => $response->body(),
            ], $response->status());
        }
    }
}
