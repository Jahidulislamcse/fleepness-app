<?php

namespace App\Webhooks\Livekit;

use Illuminate\Http\Request;
use Agence104\LiveKit\WebhookReceiver;
use Spatie\WebhookClient\WebhookConfig;
use Spatie\WebhookClient\SignatureValidator\SignatureValidator;

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

            $receiver->receive($request->getContent(), $signature);

            return true;
        } catch (\Throwable $th) {
            info('livekit webhook signature validation error', (array) $th);

            return false;
        }
    }
}
