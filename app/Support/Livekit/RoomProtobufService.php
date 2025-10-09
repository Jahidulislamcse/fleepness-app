<?php

namespace App\Support\Livekit;

use GuzzleHttp\RequestOptions;
use Illuminate\Support\Facades\Http;
use Google\Protobuf\Internal\Message;
use Livekit\RoomServiceAbstractClient;
use Illuminate\Container\Attributes\Singleton;
use Livekit\RoomService as RoomServiceContract;
use Google\Protobuf\Internal\GPBDecodeException;

#[Singleton]
class RoomProtobufService extends RoomServiceAbstractClient implements RoomServiceContract
{
    protected function doRequest(array $ctx, string $url, Message $in, Message $out): void
    {
        $body = $in->serializeToString();

        $req = $this->newRequest($ctx, $url, $body, 'application/protobuf');

        try {
            activity(__CLASS__)->withProperties([
                'method' => $req->getMethod(),
                'url' => $req->getUri(),
            ])->log('[before]: request to room service');

            $apiResponse = Http::withBody($req->getBody(), 'application/protobuf')
                ->withHeaders($req->getHeaders())
                ->send($req->getMethod(), $req->getUri(), [
                    RequestOptions::VERSION => $req->getProtocolVersion(),
                ]);

            activity(__CLASS__)->withProperties([
                'response' => $apiResponse->json(),
                'method' => $req->getMethod(),
                'url' => $req->getUri(),
            ])->log('[after]: request to room service');

            $resp = $apiResponse->toPsrResponse();

        } catch (\Throwable $e) {
            throw $this->clientError('failed to send request', $e);
        }

        if (200 !== $resp->getStatusCode()) {
            throw $this->errorFromResponse($resp);
        }

        try {
            $out->mergeFromString((string) $resp->getBody());
        } catch (GPBDecodeException $e) {
            throw $this->clientError('failed to unmarshal proto response', $e);
        }
    }
}
