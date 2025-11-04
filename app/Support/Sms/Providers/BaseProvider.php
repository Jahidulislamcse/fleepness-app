<?php

namespace App\Support\Sms\Providers;

use App\Support\Sms\Contracts\Provider;
use App\Support\Sms\Builders\SmsBuilder;
use Illuminate\Support\Traits\ForwardsCalls;

/**
 * @mixin SmsBuilder
 */
abstract class BaseProvider implements Provider
{
    use ForwardsCalls;

    public function __call($name, $arguments)
    {
        $builder = resolve(SmsBuilder::class, [
            'provider' => $this,
        ]);

        return $this->forwardCallTo($builder, $name, $arguments);
    }
}
