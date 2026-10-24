<?php

namespace Tests\Feature;

use App\Enums\DuesFrequency;
use App\Enums\MemberStatus;
use App\Models\Idir;
use App\Models\IdirSetting;
use App\Models\Member;
use App\Services\Payments\Drivers\ChapaDriver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ChapaDriverSubaccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_chapa_driver_includes_subaccount_id_when_configured()
    {
        $idir = Idir::create([
            'name' => 'Test Idir',
            'membership_basis' => 'Test',
            'region' => 'Addis',
            'sub_city' => 'Bole',
            'woreda' => '01',
            'locale' => 'am',
        ]);

        IdirSetting::create([
            'idir_id' => $idir->id,
            'dues_amount' => 100.0,
            'dues_frequency' => DuesFrequency::Monthly,
            'late_fee_amount' => 10.0,
            'late_fee_grace_days' => 5,
            'vesting_period_days' => 30,
            'required_approvals' => 1,
            'enabled_payout_triggers' => [],
            'chapa_subaccount_id' => 'TEST-SUBACCOUNT-001',
        ]);

        $member = Member::create([
            'idir_id' => $idir->id,
            'full_name' => 'John Doe',
            'phone' => '0911223344',
            'join_date' => now(),
            'status' => MemberStatus::Active,
        ]);

        Http::fake([
            'api.chapa.co/*' => Http::response([
                'status' => 'success',
                'data' => [
                    'checkout_url' => 'https://checkout.chapa.co/checkout/test',
                ],
            ], 200),
        ]);

        $driver = new ChapaDriver;
        $driver->initialize($member, 100.00, '2026-09');

        Http::assertSent(function (Request $request) {
            $data = $request->data();

            return isset($data['subaccounts']['id']) && $data['subaccounts']['id'] === 'TEST-SUBACCOUNT-001';
        });
    }
}
