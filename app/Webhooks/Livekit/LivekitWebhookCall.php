<?php

namespace App\Webhooks\Livekit;

use Illuminate\Http\Request;
use Spatie\WebhookClient\Models\WebhookCall;
use Spatie\WebhookClient\WebhookConfig;

/**
 * App\Webhooks\Livekit\LivekitWebhookCall
 *
 * @method static \Illuminate\Database\Eloquent\Builder|LivekitWebhookCall newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LivekitWebhookCall newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LivekitWebhookCall query()
 *
 * @mixin \Eloquent
 */
class LivekitWebhookCall extends WebhookCall
{
    protected $table = "webhook_calls";
    public static function storeWebhook(WebhookConfig $config, Request $request): WebhookCall
    {
        $headers = self::headersToStore($config, $request);

        return self::create([
            'name' => $config->name,
            'url' => $request->fullUrl(),
            'headers' => $headers,
            'payload' => ['raw_data' => $request->getContent()],
            'exception' => null,
        ]);
    }
}
