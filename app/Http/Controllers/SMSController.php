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

        // Ensure the number starts with '880'
        $mobileNumber = trim($validated['MobileNumbers']);

        // If the number starts with '0', remove it and prepend '880'
        if (str_starts_with($mobileNumber, '0')) {
            $mobileNumber = '880' . substr($mobileNumber, 1);
        } elseif (!str_starts_with($mobileNumber, '880')) {
            $mobileNumber = '880' . $mobileNumber;
        }

        // Update the validated data with the formatted number
        $validated['MobileNumbers'] = $mobileNumber;

        // Fetch API credentials from .env
        $apiKey = config('sms.api_key');
        $clientId = config('sms.client_id');
        $senderId = config('sms.sender_id');
        $apiUrl = config('sms.api_url') . '/SendSMS';

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
