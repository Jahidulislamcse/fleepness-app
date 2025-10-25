<?php

namespace App\Support\Sms\Factory;

use Illuminate\Support\Manager;
use Illuminate\Config\Repository;
use App\Support\Sms\Contracts\Provider;
use App\Support\Sms\Builders\SmsBuilder;
use App\Support\Sms\Providers\LogSmsProvider;
use App\Support\Sms\Providers\SmsqSmsProvider;

/**
 * @property Repository $config
 *
 * @mixin Provider
 * @mixin SmsBuilder
 */
class SmsProviderFactory extends Manager
{
    public function getDefaultDriver()
    {
        return $this->config->string('sms.driver');
    }

    public function createLogDriver()
    {
        return resolve(LogSmsProvider::class);
    }

    public function createSmsqDriver()
    {
        return resolve(SmsqSmsProvider::class);
    }
}
