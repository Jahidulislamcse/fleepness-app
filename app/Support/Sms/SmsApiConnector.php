<?php

namespace App\Support\Sms;

use SensitiveParameter;
use GuzzleHttp\Psr7\Query;
use GuzzleHttp\Psr7\Utils;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\Response;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Container\Attributes\Config;
use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Support\Traits\ForwardsCalls;

/**
 * @mixin PendingRequest
 */
class SmsApiConnector
{
    use ForwardsCalls;

    protected PendingRequest $pendingClient;

    public function setPendingClient(PendingRequest $newPendingClient): void
    {
        $this->pendingClient = $newPendingClient;
        $this->setupClientDefaults();
    }

    protected function setupClientDefaults()
    {
        return $this
            ->acceptJson()
            ->baseUrl($this->apiUrl)
            ->beforeSending(function (Request $request, array $options, PendingRequest $client) {
                $payload = $request->data();

                if ('GET' === $request->method()) {
                    $payload = Query::parse($request->toPsrRequest()->getUri()->getQuery());
                }

                data_set($payload, 'ApiKey', $this->apiKey);
                data_set($payload, 'ClientId', $this->clientId);
                data_set($payload, 'SenderId', $this->senderId);

                $changes = [];

                if ('POST' === $request->method()) {
                    if ($request->isForm()) {
                        $changes['body'] = Query::build($payload);
                    }

                    if ($request->isJson()) {
                        $changes['body'] = Json::encode($payload);
                    }

                    if ($request->isMultipart()) {
                        $changes['body'] = $payload;
                    }
                }

                if ('GET' === $request->method()) {
                    $changes['query'] = Query::build($payload);
                }

                return Utils::modifyRequest($request->toPsrRequest(), $changes);
            });
    }

    /**
     * Create a new class instance.
     */
    public function __construct(
        PendingRequest $client,

        #[Config('services.smsq.api_key')]
        #[SensitiveParameter]
        private readonly string $apiKey,
        #[Config('services.smsq.client_id')]
        #[SensitiveParameter]
        private readonly string $clientId,
        #[Config('services.smsq.sender_id')]
        #[SensitiveParameter]
        private readonly string $senderId,
        #[Config('services.smsq.api_url')]
        private readonly string $apiUrl,
    ) {
        $this->pendingClient = $client;

    }

    /**
     * SMS payload data.
     *
     * @param array{
     *     Message: string,
     *     MobileNumbers: string[]|string,
     *     Is_Unicode?:bool,
     *     Is_Flash?:bool,
     *     DataCoding?:string,
     *     ScheduleTime?:string|null,
     *     GroupId?:string
     * } $data
     */
    public function sendSms(array $data): PromiseInterface|Response
    {
        $data['MobileNumbers'] = is_array($data['MobileNumbers'])
            ? implode(',', $data['MobileNumbers'])
            : $data['MobileNumbers'];

        return $this->post('SendSMS', $data);
    }

    /**
     * @param  array{MessageParameters:list<string>}  $data
     */
    public function sendBulkSMS(array $data): PromiseInterface|Response
    {
        return $this->post('SendBulkSMS', $data);
    }

    public function __call($name, $arguments)
    {
        return $this->forwardDecoratedCallTo($this->pendingClient, $name, $arguments);
    }
}
