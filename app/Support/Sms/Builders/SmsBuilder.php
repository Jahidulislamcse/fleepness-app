<?php

namespace App\Support\Sms\Builders;

use UnexpectedValueException;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use App\Support\Sms\Contracts\Provider;
use Illuminate\Support\Facades\Validator;

class SmsBuilder
{
    private string $message = '';

    private array $mobiles = [];

    private ?Carbon $scheduleTime = null;

    /**
     * @var list<array{number:string,text:string}>
     */
    private array $bulk = [];

    public function __construct(
        private readonly Provider $provider
    ) {}

    public function withMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function withMobile(string $mobile): self
    {
        $this->mobiles = [$mobile];

        return $this;
    }

    public function withMobiles(array $mobiles): self
    {
        $this->mobiles = $mobiles;

        return $this;
    }

    public function withScheduleTime(null|Carbon|string $scheduleTime): self
    {
        $this->scheduleTime = $scheduleTime;

        return $this;
    }

    public function addMobile(string $mobile): self
    {
        $this->mobiles[] = $mobile;

        return $this;
    }

    /**
     * @param  list<array{number:string,text:string}>  $bulkMessage
     */
    public function withBulk(array $bulkMessage): self
    {
        $this->bulk = $bulkMessage;

        return $this;
    }

    public function send(): array
    {
        $validator = Validator::make([
            'message' => $this->message,
            'mobile' => array_first($this->mobiles),
            'scheduleTime' => $this->scheduleTime,
        ], [
            'message' => ['required', 'string'],
            'mobile' => ['required', 'string', 'between:11,14'],
            'scheduleTime' => ['nullable', Rule::date()],
        ]);

        throw_if(
            $validator->fails(),
            UnexpectedValueException::class,
            $validator->errors()->first()
        );

        return $this->provider->send(
            $validator->safe()->str('message')->value(),
            $validator->safe()->str('mobile')->value(),
            $validator->safe()->date('scheduleTime')
        );
    }

    public function sendBulk(): array
    {
        $validator = Validator::make([
            'bulk' => $this->bulk,
            'scheduleTime' => $this->scheduleTime,
        ], [
            'bulk' => ['required', 'array', 'min:1'],
            'bulk.*' => ['required', 'array:number,text'],
            'bulk.*.number' => ['required', 'string', 'between:11,14'],
            'bulk.*.text' => ['required', 'string'],
            'scheduleTime' => ['nullable', Rule::date()],
        ]);

        throw_if(
            $validator->fails(),
            UnexpectedValueException::class,
            $validator->errors()->first()
        );

        return $this->provider->sendBulk(
            $validator->safe()->array('bulk'),
            $validator->safe()->date('scheduleTime')
        );
    }
}
