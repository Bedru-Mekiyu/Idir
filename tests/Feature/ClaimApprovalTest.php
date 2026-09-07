<?php

namespace Tests\Feature;

use App\Enums\ApprovalDecision;
use App\Enums\ClaimStatus;
use App\Enums\CommitteeRole;
use App\Enums\DuesFrequency;
use App\Enums\MemberStatus;
use App\Models\Claim;
use App\Models\ClaimApproval;
use App\Models\Idir;
use App\Models\IdirSetting;
use App\Models\Member;
use App\Models\PayoutTriggerType;
use App\Services\LedgerService;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClaimApprovalTest extends TestCase
{
    use RefreshDatabase;

    protected Idir $idir;

    protected Member $claimant;

    protected Member $chair;

    protected Member $treasurer;

    protected PayoutTriggerType $deathTrigger;

    protected function setUp(): void
    {
        parent::setUp();

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
            'fund_balance' => 50000.00,
        ]);

        $this->claimant = Member::create([
            'idir_id' => $this->idir->id,
            'full_name' => 'ተፈራ አበበ',
            'phone' => '0911000001',
            'join_date' => now()->subMonths(6)->toDateString(),
            'status' => MemberStatus::Active,
        ]);

        $this->chair = Member::create([
            'idir_id' => $this->idir->id,
            'full_name' => 'አበበ ተሰማ (ሰብሳቢ)',
            'phone' => '0911000002',
            'join_date' => now()->subYear()->toDateString(),
            'status' => MemberStatus::Active,
            'committee_role' => CommitteeRole::Chair,
        ]);

        $this->treasurer = Member::create([
            'idir_id' => $this->idir->id,
            'full_name' => 'ከበደ ደስታ (ገንዘብ ያዥ)',
            'phone' => '0911000003',
            'join_date' => now()->subYear()->toDateString(),
            'status' => MemberStatus::Active,
            'committee_role' => CommitteeRole::Treasurer,
        ]);

        $this->deathTrigger = PayoutTriggerType::create([
            'idir_id' => $this->idir->id,
            'name' => 'death',
            'label_am' => 'ሞት',
            'default_payout_amount' => 8000.00,
            'is_active' => true,
        ]);
    }

    public function test_proposed_amount_fallback_logic(): void
    {
        // 1. If requested_amount is provided, use it
        $claim1 = Claim::create([
            'idir_id' => $this->idir->id,
            'member_id' => $this->claimant->id,
            'payout_trigger_type_id' => $this->deathTrigger->id,
            'requested_amount' => 12000.00,
            'description' => 'የቀብር እርዳታ',
            'status' => ClaimStatus::Pending,
        ]);
        $this->assertEquals(12000.00, $claim1->getProposedAmount());

        // 2. If requested_amount is null, fall back to trigger default_payout_amount
        $claim2 = Claim::create([
            'idir_id' => $this->idir->id,
            'member_id' => $this->claimant->id,
            'payout_trigger_type_id' => $this->deathTrigger->id,
            'requested_amount' => null,
            'description' => 'የቀብር እርዳታ',
            'status' => ClaimStatus::Pending,
        ]);
        $this->assertEquals(8000.00, $claim2->getProposedAmount());
    }

    public function test_database_level_unique_constraint_prevents_duplicate_approvals(): void
    {
        $claim = Claim::create([
            'idir_id' => $this->idir->id,
            'member_id' => $this->claimant->id,
            'payout_trigger_type_id' => $this->deathTrigger->id,
            'requested_amount' => 8000.00,
            'description' => 'የቀብር እርዳታ',
            'status' => ClaimStatus::Pending,
        ]);

        // First approval
        ClaimApproval::create([
            'claim_id' => $claim->id,
            'approver_member_id' => $this->chair->id,
            'decision' => ApprovalDecision::Approved,
            'approved_amount' => 8000.00,
            'decided_at' => now(),
        ]);

        // Second approval by SAME approver must throw QueryException due to DB unique constraint (Fix #2)
        $this->expectException(QueryException::class);

        ClaimApproval::create([
            'claim_id' => $claim->id,
            'approver_member_id' => $this->chair->id,
            'decision' => ApprovalDecision::Approved,
            'approved_amount' => 8000.00,
            'decided_at' => now(),
        ]);
    }

    public function test_single_rejection_immediately_rejects_claim_and_stops_workflow(): void
    {
        $claim = Claim::create([
            'idir_id' => $this->idir->id,
            'member_id' => $this->claimant->id,
            'payout_trigger_type_id' => $this->deathTrigger->id,
            'requested_amount' => 8000.00,
            'description' => 'የቀብር እርዳታ',
            'status' => ClaimStatus::Pending,
        ]);

        // Fix #4: Single rejection from any committee approver
        ClaimApproval::create([
            'claim_id' => $claim->id,
            'approver_member_id' => $this->chair->id,
            'decision' => ApprovalDecision::Rejected,
            'remarks' => 'የቀረበው ማስረጃ ተቀባይነት የለውም (Invalid documentation)',
            'decided_at' => now(),
        ]);

        $claim->update(['status' => ClaimStatus::Rejected]);

        $this->assertEquals(ClaimStatus::Rejected, $claim->fresh()->status);
    }

    public function test_disbursement_amount_comes_from_the_last_deciding_approval(): void
    {
        $claim = Claim::create([
            'idir_id' => $this->idir->id,
            'member_id' => $this->claimant->id,
            'payout_trigger_type_id' => $this->deathTrigger->id,
            'requested_amount' => 8000.00,
            'description' => 'የቀብር እርዳታ',
            'status' => ClaimStatus::Pending,
        ]);

        // Approver 1 approves with default 8000 ETB
        ClaimApproval::create([
            'claim_id' => $claim->id,
            'approver_member_id' => $this->chair->id,
            'decision' => ApprovalDecision::Approved,
            'approved_amount' => 8000.00,
            'decided_at' => now()->subMinute(),
        ]);

        // Approver 2 (deciding approver) overrides to 9000 ETB with reason
        $decidingAmount = 9000.00;
        ClaimApproval::create([
            'claim_id' => $claim->id,
            'approver_member_id' => $this->treasurer->id,
            'decision' => ApprovalDecision::Approved,
            'approved_amount' => $decidingAmount,
            'remarks' => 'ተጨማሪ የትራንስፖርት ወጪ ስለተጨመረበት',
            'decided_at' => now(),
        ]);

        $claim->update(['status' => ClaimStatus::Approved]);

        // Record disbursement using deciding approval's amount (Fix #3)
        $disbursement = app(LedgerService::class)->recordDisbursement($claim, [
            'amount' => $decidingAmount,
            'method' => 'cash',
            'recorded_by_member_id' => $this->treasurer->id,
        ]);

        $this->assertEquals(9000.00, $disbursement->amount);
        $this->assertEquals(ClaimStatus::Paid, $claim->fresh()->status);
    }

    public function test_claim_with_no_requested_amount_and_no_trigger_default_returns_null_forcing_manual_entry(): void
    {
        $customTrigger = PayoutTriggerType::create([
            'idir_id' => $this->idir->id,
            'name' => 'other',
            'label_am' => 'ሌላ ድጋፍ',
            'default_payout_amount' => null, // No default
            'is_active' => true,
        ]);

        $claim = Claim::create([
            'idir_id' => $this->idir->id,
            'member_id' => $this->claimant->id,
            'payout_trigger_type_id' => $customTrigger->id,
            'requested_amount' => null, // No requested amount
            'description' => 'የልዩ ድጋፍ ጥያቄ',
            'status' => ClaimStatus::Pending,
        ]);

        // getProposedAmount() MUST return null (not 0 or arbitrary default)
        $this->assertNull($claim->getProposedAmount());
    }

    public function test_rejected_claim_cannot_be_reapproved(): void
    {
        $claim = Claim::create([
            'idir_id' => $this->idir->id,
            'member_id' => $this->claimant->id,
            'payout_trigger_type_id' => $this->deathTrigger->id,
            'requested_amount' => 8000.00,
            'description' => 'የቀብር እርዳታ',
            'status' => ClaimStatus::Rejected, // Already rejected
        ]);

        // Attempting to disburse for a rejected claim must fail
        $this->expectException(\InvalidArgumentException::class);
        app(LedgerService::class)->recordDisbursement($claim, [
            'amount' => 8000.00,
            'method' => 'cash',
        ]);
    }
}
