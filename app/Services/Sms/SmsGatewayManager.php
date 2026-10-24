<?php

namespace App\Services\Sms;

use App\Services\Sms\Drivers\AfricasTalkingDriver;
use App\Services\Sms\Drivers\AfroMessageDriver;
use App\Services\Sms\Drivers\GeezSmsDriver;
use Illuminate\Support\Manager;

class SmsGatewayManager extends Manager
{
    public function getDefaultDriver(): string
    {
        return config('services.sms.driver', 'afromessage');
    }

    public function createAfromessageDriver(): SmsGatewayInterface
    {
        return new AfroMessageDriver;
    }

    public function createGeezsmsDriver(): SmsGatewayInterface
    {
        return new GeezSmsDriver;
    }

    public function createAfricastalkingDriver(): SmsGatewayInterface
    {
        return new AfricasTalkingDriver;
    }
}
