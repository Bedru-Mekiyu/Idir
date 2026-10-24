<?php

namespace Tests\Feature\Payments;

use App\Models\Idir;
use App\Models\Member;
use App\Models\User;
use App\Services\Payments\Drivers\TelebirrDriver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TelebirrDriverTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_configured_false_if_missing_credentials()
    {
        config(['services.telebirr.app_id' => '']);

        $driver = new TelebirrDriver;
        $user = User::create(['name' => 'Test', 'email' => 't2@example.com', 'password' => 'pwd', 'phone' => '0911000000']);
        $idir = Idir::create(['name' => 'Test Idir', 'locale' => 'am']);
        $member = Member::create(['idir_id' => $idir->id, 'user_id' => $user->id, 'full_name' => 'Test', 'phone' => '0911000000', 'join_date' => '2023-01-01', 'status' => 'active']);

        $result = $driver->initialize($member, 100, '2023-01');

        $this->assertEquals('error', $result['status']);
        $this->assertFalse($result['configured']);
    }

    public function test_it_initializes_payment_with_fake_http()
    {
        config([
            'services.telebirr.app_id' => 'test-id',
            'services.telebirr.app_key' => 'test-key',
            'services.telebirr.short_code' => '12345',
            'services.telebirr.public_key' => '',
        ]);

        Http::fake([
            '*.ethiomobilemoney.et*' => Http::response([
                'code' => '0',
                'data' => ['toPayUrl' => 'https://pay.telebirr.et/test'],
            ], 200),
        ]);

        $driver = new TelebirrDriver;
        $user = User::create(['name' => 'Test', 'email' => 't1@example.com', 'password' => 'pwd', 'phone' => '0911000000']);
        $idir = Idir::create(['name' => 'Test Idir', 'locale' => 'am']);
        $member = Member::create(['idir_id' => $idir->id, 'user_id' => $user->id, 'full_name' => 'Test', 'phone' => '0911000000', 'join_date' => '2023-01-01', 'status' => 'active']);

        $result = $driver->initialize($member, 100, '2023-01');

        $this->assertEquals('success', $result['status']);
        $this->assertEquals('https://pay.telebirr.et/test', $result['data']['checkout_url']);

        $this->assertDatabaseHas('contributions', [
            'member_id' => $member->id,
            'method' => 'telebirr',
            'amount' => 100,
        ]);
    }
}
