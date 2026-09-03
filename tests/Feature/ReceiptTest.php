<?php

namespace Tests\Feature;

use App\Enums\DuesFrequency;
use App\Enums\MemberStatus;
use App\Enums\PaymentMethod;
use App\Models\Contribution;
use App\Models\Idir;
use App\Models\IdirSetting;
use App\Models\Member;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReceiptTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_render_official_printable_receipt(): void
    {
        $idir = Idir::create([
            'name' => 'ሰላም የሰፈር እድር',
            'region' => 'አዲስ አበባ',
            'sub_city' => 'ቦሌ',
            'woreda' => 'ወረዳ 03',
            'locale' => 'am',
        ]);

        IdirSetting::create([
            'idir_id' => $idir->id,
            'dues_amount' => 200.00,
            'dues_frequency' => DuesFrequency::Monthly,
            'vesting_period_days' => 90,
            'required_approvals' => 2,
            'fund_balance' => 200.00,
        ]);

        $member = Member::create([
            'idir_id' => $idir->id,
            'full_name' => 'አበበ ተሰማ ደስታ',
            'phone' => '0911223344',
            'join_date' => '2024-01-01',
            'status' => MemberStatus::Active,
        ]);

        $contribution = Contribution::create([
            'idir_id' => $idir->id,
            'member_id' => $member->id,
            'amount' => 200.00,
            'method' => PaymentMethod::Cash,
            'period_covered' => '2026-08',
            'notes' => 'በጥሬ ገንዘብ የተከፈለ',
        ]);

        $response = $this->get("/member/receipt/{$contribution->id}");

        $response->assertStatus(200);
        $response->assertSee('ሰላም የሰፈር እድር');
        $response->assertSee('አበበ ተሰማ ደስታ');
        $response->assertSee('200.00 ብር');
        $response->assertSee('2026-08');
        $response->assertSee('የመዋጮ ደረሰኝ');
    }
}
