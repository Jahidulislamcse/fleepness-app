<?php

namespace App\Webhooks\Livekit;

use Agence104\LiveKit\WebhookReceiver;
use Illuminate\Http\Request;
use Livekit\WebhookEvent;
use Spatie\WebhookClient\SignatureValidator\SignatureValidator;
use Spatie\WebhookClient\WebhookConfig;

class LivekitSignatureValidator implements SignatureValidator
{
    public function isValid(Request $request, WebhookConfig $config): bool
    {
        // return true; 

        $signature = $request->header($config->signatureHeaderName);

        if (! $signature) {
            return false;
        }

        try {
            $receiver = new WebhookReceiver(config('services.livekit.api_key'), config('services.livekit.api_secret'));

            $event = $receiver->receive($request->getContent(), $signature);

            // Validate Sha256.
            return $event instanceof WebhookEvent;
        } catch (\Throwable $th) {
            info('livekit webhook signature validation error', (array) $th);

            return false;
        }
    }
}
