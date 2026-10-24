<?php

namespace Tests\Feature\Sms;

use App\Services\Sms\Drivers\GeezSmsDriver;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GeezSmsDriverTest extends TestCase
{
    public function test_it_returns_configured_false_if_no_token()
    {
        config(['services.geezsms.token' => '']);

        $driver = new GeezSmsDriver;
        $result = $driver->sendSms('0911000000', 'Test message');

        $this->assertFalse($result['configured']);
        $this->assertEquals('error', $result['acknowledge']);
    }

    public function test_it_sends_sms_successfully()
    {
        config(['services.geezsms.token' => 'test-token']);

        Http::fake([
            '*.geezsms.com*' => Http::response(['status' => 200, 'message' => 'success'], 200),
        ]);

        $driver = new GeezSmsDriver;
        $result = $driver->sendSms('0911000000', 'Test message');

        $this->assertTrue($result['configured']);
        $this->assertEquals('success', $result['acknowledge']);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'geezsms.com')
                && isset($request['token'])
                && isset($request['contacts'])
                && isset($request['msg'])
                && ! str_starts_with($request['contacts'][0]['phone_number'], '+'); // GeezSMS expects no leading +
        });
    }
}
