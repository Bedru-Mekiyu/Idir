<?php

namespace Tests\Feature\Sms;

use App\Services\Sms\Drivers\AfricasTalkingDriver;
use App\Services\Sms\Drivers\AfroMessageDriver;
use App\Services\Sms\Drivers\GeezSmsDriver;
use App\Services\Sms\SmsGatewayManager;
use Tests\TestCase;

class SmsGatewayManagerTest extends TestCase
{
    public function test_it_resolves_geezsms_driver()
    {
        config(['services.sms.driver' => 'geezsms']);
        $manager = $this->app->make(SmsGatewayManager::class);
        $this->assertInstanceOf(GeezSmsDriver::class, $manager->driver());
    }

    public function test_it_resolves_africastalking_driver()
    {
        config(['services.sms.driver' => 'africastalking']);
        $manager = $this->app->make(SmsGatewayManager::class);
        $this->assertInstanceOf(AfricasTalkingDriver::class, $manager->driver());
    }

    public function test_it_resolves_afromessage_driver()
    {
        config(['services.sms.driver' => 'afromessage']);
        $manager = $this->app->make(SmsGatewayManager::class);
        $this->assertInstanceOf(AfroMessageDriver::class, $manager->driver());
    }
}
