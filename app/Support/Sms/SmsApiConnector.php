<?php

namespace App\Support\Sms;

use ArrayIterator;
use SensitiveParameter;
use GuzzleHttp\Psr7\Utils;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\Response;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Container\Attributes\Config;
use Illuminate\Support\Traits\ForwardsCalls;

/**
 * @mixin PendingRequest
 */
class SmsApiConnector
{
    use ForwardsCalls;

    /**
     * Create a new class instance.
     */
    public function __construct(
        protected PendingRequest $client,

        #[Config('sms.api_key')]
        #[SensitiveParameter]
        private readonly string $apiKey,
        #[Config('sms.client_id')]
        #[SensitiveParameter]
        private readonly string $clientId,
        #[Config('sms.sender_id')]
        #[SensitiveParameter]
        private readonly string $senderId,
        #[Config('sms.api_url')]
        private readonly string $apiUrl,
    ) {
        $this
            ->baseUrl($this->apiUrl)
            ->acceptJson()
            ->beforeSending(function (Request $request, array $options, PendingRequest $client) {
                $payload = $request->data();
                data_set($payload, 'ApiKey', $this->apiKey);
                data_set($payload, 'ClientId', $this->clientId);
                data_set($payload, 'SenderId', $this->senderId);

                return Utils::modifyRequest($request->toPsrRequest(), [
                    'body' => new ArrayIterator($payload),
                ]);
            });
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
     * @param  array{MessageParameters:list<string>}  $messages
     */
    public function sendBulkSMS(array $data): PromiseInterface|Response
    {
        return $this->post('SendBulkSMS', $data);
    }

    public function __call($name, $arguments)
    {
        return $this->forwardDecoratedCallTo($this->client, $name, $arguments);
    }
}
