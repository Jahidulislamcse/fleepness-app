<?php

namespace App\Support\Sms\Contracts;

use Illuminate\Support\Carbon;

interface Provider
{
    /**
     * @param  string|list<string>  $mobiles
     * @return array<string,mixed>
     */
    public function send(string $message, array|string $mobiles, null|Carbon|string $scheduleTime): array;

    /**
     * @param  list<array{number:string,text:string}>  $bulkMessage
     * @return array<string,mixed>
     */
    public function sendBulk(array $bulkMessage, null|Carbon|string $scheduleTime): array;
}
