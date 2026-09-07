<?php

namespace Tests\Feature;

use App\Enums\ChapaStatus;
use App\Enums\ContributionType;
use App\Enums\DuesFrequency;
use App\Enums\MemberStatus;
use App\Enums\PaymentMethod;
use App\Models\Contribution;
use App\Models\Idir;
use App\Models\IdirSetting;
use App\Models\Member;
use App\Services\LedgerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LedgerTest extends TestCase
{
    use RefreshDatabase;

    protected Idir $idir;

    protected Member $member;

    protected LedgerService $ledger;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ledger = app(LedgerService::class);

        $this->idir = Idir::create([
            'name' => 'ሙከራ ሰላም እድር',
            'locale' => 'am',
        ]);

        IdirSetting::create([
            'idir_id' => $this->idir->id,
            'dues_amount' => 200.00,
            'dues_frequency' => DuesFrequency::Monthly,
            'vesting_period_days' => 90,
            'required_approvals' => 2,
            'fund_balance' => 0.00,
        ]);

        $this->member = Member::create([
            'idir_id' => $this->idir->id,
            'full_name' => 'ተፈራ አበበ',
            'phone' => '0911000001',
            'join_date' => now()->subMonths(6)->toDateString(),
            'status' => MemberStatus::Active,
        ]);
    }

    public function test_can_record_contribution_and_updates_fund_balance(): void
    {
        $contribution = $this->ledger->recordContribution([
            'idir_id' => $this->idir->id,
            'member_id' => $this->member->id,
            'amount' => 200.00,
            'method' => PaymentMethod::Cash,
            'type' => ContributionType::Cash,
            'period_covered' => '2026-08',
            'is_correction' => false,
        ]);

        $this->assertDatabaseHas('contributions', [
            'id' => $contribution->id,
            'amount' => 200.00,
            'is_correction' => false,
        ]);

        $this->assertEquals(200.00, $this->idir->settings->fresh()->fund_balance);
    }

    public function test_record_correction_creates_offsetting_entry_with_is_correction_flag(): void
    {
        // 1. Initial contribution
        $original = $this->ledger->recordContribution([
            'idir_id' => $this->idir->id,
            'member_id' => $this->member->id,
            'amount' => 200.00,
            'method' => PaymentMethod::Cash,
            'type' => ContributionType::Cash,
            'period_covered' => '2026-08',
        ]);

        $this->assertEquals(200.00, $this->idir->settings->fresh()->fund_balance);

        // 2. Record correction (Fix #1: explicit is_correction boolean flag)
        $correction = $this->ledger->recordCorrection($original, 'የተሳሳተ የደረሰኝ ቁጥር');

        $this->assertTrue($correction->is_correction);
        $this->assertEquals(-200.00, $correction->amount);
        $this->assertEquals($original->id, $correction->corrected_contribution_id);

        // Balance should be back to 0
        $this->assertEquals(0.00, $this->idir->settings->fresh()->fund_balance);

        // Original record is unchanged
        $this->assertDatabaseHas('contributions', [
            'id' => $original->id,
            'amount' => 200.00,
            'is_correction' => false,
        ]);
    }

    public function test_pending_chapa_payments_are_not_counted_in_fund_balance(): void
    {
        Contribution::create([
            'idir_id' => $this->idir->id,
            'member_id' => $this->member->id,
            'amount' => 500.00,
            'method' => PaymentMethod::Chapa,
            'type' => ContributionType::Cash,
            'period_covered' => '2026-08',
            'chapa_status' => ChapaStatus::Pending,
            'is_correction' => false,
        ]);

        $this->ledger->recalculateFundBalance($this->idir->id);

        $this->assertEquals(0.00, $this->idir->settings->fresh()->fund_balance);
    }

    public function test_verified_chapa_payments_are_counted_in_fund_balance(): void
    {
        Contribution::create([
            'idir_id' => $this->idir->id,
            'member_id' => $this->member->id,
            'amount' => 500.00,
            'method' => PaymentMethod::Chapa,
            'type' => ContributionType::Cash,
            'period_covered' => '2026-08',
            'chapa_status' => ChapaStatus::Verified,
            'is_correction' => false,
        ]);

        $this->ledger->recalculateFundBalance($this->idir->id);

        $this->assertEquals(500.00, $this->idir->settings->fresh()->fund_balance);
    }

    public function test_member_vesting_period_rule(): void
    {
        // 6 months tenure > 90 days vesting
        $this->assertTrue($this->ledger->isVested($this->member));

        // New member (10 days tenure < 90 days vesting)
        $newMember = Member::create([
            'idir_id' => $this->idir->id,
            'full_name' => 'አዲስ አባል',
            'phone' => '0911000002',
            'join_date' => now()->subDays(10)->toDateString(),
            'status' => MemberStatus::Active,
        ]);

        $this->assertFalse($this->ledger->isVested($newMember));
    }
}
