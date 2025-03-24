<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SMSService
{
    protected $apiKey;
    protected $clientId;
    protected $senderId;
    protected $apiUrl;

    public function __construct()
    {
        $this->apiKey = config('sms.api_key');
        $this->clientId = config('sms.client_id');
        $this->senderId = config('sms.sender_id');
        $this->apiUrl = config('sms.api_url');
    }

    /**
     * Send a single SMS
     *
     * @param string $message
     * @param string $mobileNumber
     * @return array
     */
    public function sendSMS($message, $mobileNumber)
    {

        $url = "{$this->apiUrl}/SendSMS";

        $payload = [
            "SenderId" => $this->senderId,
            "Message" => $message,
            "MobileNumbers" => $mobileNumber,
            "ApiKey" => $this->apiKey,
            "ClientId" => $this->clientId
        ];

        // Log the payload before sending the request
        \Log::info('Sending SMS Payload:', $payload);

        $response = Http::post($url, $payload);

        // Log the response for debugging
        \Log::info('SMS API Response:', $response->json());

        return $response->json();
    }

    /**
     * Send bulk SMS
     *
     * @param array $messages
     * @return array
     */
    public function sendBulkSMS(array $messages)
    {
        $url = "{$this->apiUrl}/SendBulkSMS";

        $payload = [
            "SenderId" => $this->senderId,
            "ApiKey" => $this->apiKey,
            "ClientId" => $this->clientId,
            "MessageParameters" => $messages
        ];

        $response = Http::post($url, $payload);

        return $response->json();
    }
}
