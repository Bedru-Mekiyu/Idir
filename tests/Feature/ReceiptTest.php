<?php

namespace Tests\Feature;

use App\Enums\CommitteeRole;
use App\Enums\DuesFrequency;
use App\Enums\MemberStatus;
use App\Enums\PaymentMethod;
use App\Models\Contribution;
use App\Models\Idir;
use App\Models\IdirSetting;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReceiptTest extends TestCase
{
    use RefreshDatabase;

    private Idir $idir;

    private Member $member;

    private Contribution $contribution;

    protected function setUp(): void
    {
        parent::setUp();

        $this->idir = Idir::create([
            'name' => 'ሰላም የሰፈር እድር',
            'region' => 'አዲስ አበባ',
            'sub_city' => 'ቦሌ',
            'woreda' => 'ወረዳ 03',
            'locale' => 'am',
        ]);

        IdirSetting::create([
            'idir_id' => $this->idir->id,
            'dues_amount' => 200.00,
            'dues_frequency' => DuesFrequency::Monthly,
            'vesting_period_days' => 90,
            'required_approvals' => 2,
            'fund_balance' => 200.00,
        ]);

        $ownerUser = User::create([
            'name' => 'አበበ ተሰማ ደስታ',
            'phone' => '0911223344',
            'password' => bcrypt('password'),
        ]);

        $this->member = Member::create([
            'idir_id' => $this->idir->id,
            'user_id' => $ownerUser->id,
            'full_name' => 'አበበ ተሰማ ደስታ',
            'phone' => '0911223344',
            'join_date' => '2024-01-01',
            'status' => MemberStatus::Active,
        ]);

        $this->contribution = Contribution::create([
            'idir_id' => $this->idir->id,
            'member_id' => $this->member->id,
            'amount' => 200.00,
            'method' => PaymentMethod::Cash,
            'period_covered' => '2026-08',
            'notes' => 'በጥሬ ገንዘብ የተከፈለ',
        ]);
    }

    public function test_unauthenticated_visitor_is_redirected_away_from_receipt(): void
    {
        $this->get("/member/receipt/{$this->contribution->id}")
            ->assertRedirect('/member/login');
    }

    public function test_contributing_member_can_view_own_receipt(): void
    {
        $this->actingAs($this->member->user)
            ->get("/member/receipt/{$this->contribution->id}")
            ->assertStatus(200)
            ->assertSee('ሰላም የሰፈር እድር')
            ->assertSee('አበበ ተሰማ ደስታ')
            ->assertSee('200.00 ብር');
    }

    public function test_committee_member_of_same_idir_can_view_receipt(): void
    {
        $committeeUser = User::create([
            'name' => 'ሰብሳቢ',
            'phone' => '0911998877',
            'password' => bcrypt('password'),
        ]);

        Member::create([
            'idir_id' => $this->idir->id,
            'user_id' => $committeeUser->id,
            'full_name' => 'ሰብሳቢ',
            'phone' => '0911998877',
            'join_date' => '2024-01-01',
            'status' => MemberStatus::Active,
            'committee_role' => CommitteeRole::Chair,
        ]);

        $this->actingAs($committeeUser)
            ->get("/member/receipt/{$this->contribution->id}")
            ->assertStatus(200)
            ->assertSee('አበበ ተሰማ ደስታ');
    }

    public function test_member_of_another_idir_cannot_view_receipt(): void
    {
        $otherIdir = Idir::create(['name' => 'ሌላ እድር', 'locale' => 'am']);
        $otherUser = User::create([
            'name' => 'ሌላ አባል',
            'phone' => '0911000011',
            'password' => bcrypt('password'),
        ]);

        Member::create([
            'idir_id' => $otherIdir->id,
            'user_id' => $otherUser->id,
            'full_name' => 'ሌላ አባል',
            'phone' => '0911000011',
            'join_date' => '2024-01-01',
            'status' => MemberStatus::Active,
        ]);

        $this->actingAs($otherUser)
            ->get("/member/receipt/{$this->contribution->id}")
            ->assertForbidden();
    }
}
