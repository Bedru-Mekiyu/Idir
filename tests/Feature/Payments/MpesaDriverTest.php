<?php

namespace Tests\Feature\Payments;

use App\Models\Idir;
use App\Models\Member;
use App\Models\User;
use App\Services\Payments\Drivers\MpesaDriver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MpesaDriverTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_configured_false_if_missing_credentials()
    {
        config(['services.mpesa.consumer_key' => '']);

        $driver = new MpesaDriver;
        $user = User::create(['name' => 'Test', 'email' => 'm1@example.com', 'password' => 'pwd', 'phone' => '0911000000']);
        $idir = Idir::create(['name' => 'Test Idir', 'locale' => 'am']);
        $member = Member::create(['idir_id' => $idir->id, 'user_id' => $user->id, 'full_name' => 'Test', 'phone' => '0911000000', 'join_date' => '2023-01-01', 'status' => 'active']);

        $result = $driver->initialize($member, 100, '2023-01');

        $this->assertEquals('error', $result['status']);
        $this->assertFalse($result['configured']);
    }

    public function test_it_returns_configured_false_if_passkey_missing()
    {
        config([
            'services.mpesa.consumer_key' => 'test-key',
            'services.mpesa.consumer_secret' => 'test-secret',
            'services.mpesa.shortcode' => '174379',
            'services.mpesa.passkey' => '',
        ]);

        $driver = new MpesaDriver;
        $user = User::create(['name' => 'Test', 'email' => 'm1_pass@example.com', 'password' => 'pwd', 'phone' => '0911000000']);
        $idir = Idir::create(['name' => 'Test Idir', 'locale' => 'am']);
        $member = Member::create(['idir_id' => $idir->id, 'user_id' => $user->id, 'full_name' => 'Test', 'phone' => '0911000000', 'join_date' => '2023-01-01', 'status' => 'active']);

        $result = $driver->initialize($member, 100, '2023-01');

        $this->assertEquals('error', $result['status']);
        $this->assertFalse($result['configured']);
        $this->assertStringContainsString('MPESA_PASSKEY', $result['message']);
    }

    public function test_it_initializes_payment_with_fake_http()
    {
        config([
            'services.mpesa.consumer_key' => 'test-key',
            'services.mpesa.consumer_secret' => 'test-secret',
            'services.mpesa.shortcode' => '174379',
            'services.mpesa.passkey' => 'test-pass',
        ]);

        Http::fake([
            '*/oauth/v1/generate*' => Http::response(['access_token' => 'test-token'], 200),
            '*/mpesa/stkpush/v1/processrequest' => Http::response([
                'ResponseCode' => '0',
                'CheckoutRequestID' => 'test-checkout-id',
                'CustomerMessage' => 'Success',
            ], 200),
        ]);

        $driver = new MpesaDriver;
        $user = User::create(['name' => 'Test', 'email' => 'm2@example.com', 'password' => 'pwd', 'phone' => '0911000000']);
        $idir = Idir::create(['name' => 'Test Idir', 'locale' => 'am']);
        $member = Member::create(['idir_id' => $idir->id, 'user_id' => $user->id, 'full_name' => 'Test', 'phone' => '0911000000', 'join_date' => '2023-01-01', 'status' => 'active']);

        $result = $driver->initialize($member, 100, '2023-01');

        $this->assertEquals('success', $result['status']);
        $this->assertEquals('test-checkout-id', $result['data']['checkout_request_id']);

        $this->assertDatabaseHas('contributions', [
            'member_id' => $member->id,
            'method' => 'mpesa',
            'amount' => 100,
            'gateway_reference' => 'test-checkout-id',
        ]);
    }
}
