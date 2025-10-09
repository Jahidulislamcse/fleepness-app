<?php

namespace App\Support\Livekit;

use Livekit\Egress;
use GuzzleHttp\RequestOptions;
use Livekit\EgressAbstractClient;
use Illuminate\Support\Facades\Http;
use Google\Protobuf\Internal\Message;
use Illuminate\Container\Attributes\Singleton;
use Google\Protobuf\Internal\GPBDecodeException;

#[Singleton]
class EgressJsonService extends EgressAbstractClient implements Egress
{
    protected function doRequest(array $ctx, string $url, Message $in, Message $out): void
    {
        $body = $in->serializeToJsonString();

        $req = $this->newRequest($ctx, $url, $body, 'application/json');

        try {
            $resp = Http::acceptJson()
                ->withBody($req->getBody())
                ->withHeaders($req->getHeaders())
                ->send($req->getMethod(), $req->getUri(), [
                    RequestOptions::VERSION => $req->getProtocolVersion(),
                ])
                ->toPsrResponse();
        } catch (\Throwable $e) {
            throw $this->clientError('failed to send request', $e);
        }

        if (200 !== $resp->getStatusCode()) {
            throw $this->errorFromResponse($resp);
        }

        try {
            $out->mergeFromJsonString((string) $resp->getBody());
        } catch (GPBDecodeException $e) {
            throw $this->clientError('failed to unmarshal json response', $e);
        }
    }
}
