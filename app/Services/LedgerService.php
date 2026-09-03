<?php

namespace App\Services;

use App\Enums\ChapaStatus;
use App\Enums\ClaimStatus;
use App\Enums\ContributionType;
use App\Enums\MemberStatus;
use App\Enums\PaymentMethod;
use App\Models\Claim;
use App\Models\Contribution;
use App\Models\Disbursement;
use App\Models\IdirSetting;
use App\Models\Member;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LedgerService
{
    /**
     * Record a contribution. Returns the immutable contribution record.
     */
    public function recordContribution(array $data): Contribution
    {
        return DB::transaction(function () use ($data) {
            // Default is_correction to false if not specified
            $data['is_correction'] = $data['is_correction'] ?? false;

            $contribution = Contribution::create($data);

            // Recalculate fund balance
            $balance = $this->recalculateFundBalance($contribution->idir_id);

            // Update member arrears status if caught up
            $this->updateMemberArrearsStatus($contribution->member_id);

            Log::info('Ledger: Contribution recorded', [
                'idir_id' => $contribution->idir_id,
                'member_id' => $contribution->member_id,
                'amount' => $contribution->amount,
                'method' => $contribution->method->value ?? 'cash',
                'period' => $contribution->period_covered,
                'is_correction' => $contribution->is_correction,
                'new_balance' => $balance,
            ]);

            return $contribution;
        });
    }

    /**
     * Record a correction (offsetting entry). Never mutates existing history.
     * Fix #1: Sets boolean is_correction = true explicitly on the record.
     */
    public function recordCorrection(Contribution $original, string $reason, ?int $recordedByMemberId = null): Contribution
    {
        return DB::transaction(function () use ($original, $reason, $recordedByMemberId) {
            $correction = Contribution::create([
                'idir_id' => $original->idir_id,
                'member_id' => $original->member_id,
                'paid_by_member_id' => $original->paid_by_member_id,
                'recorded_by_member_id' => $recordedByMemberId ?? auth()->user()?->member?->id,
                'amount' => -$original->amount, // Offsetting negative amount
                'method' => $original->method,
                'type' => $original->type,
                'period_covered' => $original->period_covered,
                'notes' => "ማስተካከያ ለክፍያ #{$original->id}፦ {$reason}",
                'is_correction' => true, // Fix #1: Explicit boolean flag
                'corrected_contribution_id' => $original->id,
            ]);

            $balance = $this->recalculateFundBalance($original->idir_id);
            $this->updateMemberArrearsStatus($original->member_id);

            Log::info('Ledger: Correction recorded', [
                'idir_id' => $original->idir_id,
                'original_contribution_id' => $original->id,
                'correction_id' => $correction->id,
                'offset_amount' => $correction->amount,
                'reason' => $reason,
                'new_balance' => $balance,
            ]);

            return $correction;
        });
    }

    /**
     * Record a disbursement. Only callable when a claim is fully approved.
     * 
     * Business Rule (Fix #3):
     * The final disbursements.amount is the amount from the LAST (deciding) approval —
     * i.e., whichever approval crosses the required-approvals threshold sets the paid amount.
     */
    public function recordDisbursement(Claim $claim, array $data): Disbursement
    {
        return DB::transaction(function () use ($claim, $data) {
            // Ensure claim is approved
            if ($claim->status !== ClaimStatus::Approved && $claim->status !== ClaimStatus::Paid) {
                Log::warning('Ledger: Disbursement rejected for non-approved claim', [
                    'claim_id' => $claim->id,
                    'status' => $claim->status->value,
                ]);
                throw new \InvalidArgumentException('Disbursement can only be recorded for approved claims.');
            }

            $disbursement = Disbursement::create([
                'idir_id' => $claim->idir_id,
                'claim_id' => $claim->id,
                'member_id' => $claim->member_id,
                'amount' => $data['amount'], // Amount from deciding approval
                'method' => $data['method'] ?? 'cash',
                'recorded_by_member_id' => $data['recorded_by_member_id'] ?? auth()->user()?->member?->id,
                'notes' => $data['notes'] ?? null,
            ]);

            $claim->update(['status' => ClaimStatus::Paid]);

            $balance = $this->recalculateFundBalance($claim->idir_id);

            Log::info('Ledger: Disbursement executed', [
                'idir_id' => $claim->idir_id,
                'claim_id' => $claim->id,
                'recipient_member_id' => $claim->member_id,
                'amount' => $disbursement->amount,
                'new_balance' => $balance,
            ]);

            return $disbursement;
        });
    }

    /**
     * Recalculate fund balance from the immutable ledger.
     * Immutable ledger sum is the single source of truth — never increment/decrement directly.
     */
    public function recalculateFundBalance(int $idirId): float
    {
        // Sum verified digital contributions and all cash/in-kind contributions
        $totalContributions = (float) Contribution::where('idir_id', $idirId)
            ->where(function ($q) {
                $q->whereNull('chapa_status')
                  ->orWhere('chapa_status', ChapaStatus::Verified->value);
            })
            ->sum('amount');

        // Sum all disbursements
        $totalDisbursements = (float) Disbursement::where('idir_id', $idirId)
            ->sum('amount');

        $balance = $totalContributions - $totalDisbursements;

        IdirSetting::where('idir_id', $idirId)->update([
            'fund_balance' => $balance,
        ]);

        return $balance;
    }

    /**
     * Check if a member has met the vesting period requirement.
     */
    public function isVested(Member $member): bool
    {
        return $member->isVested();
    }

    /**
     * Update member arrears status based on recent payments.
     */
    public function updateMemberArrearsStatus(int $memberId): void
    {
        $member = Member::with('idir.settings')->find($memberId);
        if (!$member || $member->status === MemberStatus::Excluded) {
            return;
        }

        $currentPeriod = now()->format('Y-m');
        $hasPaidCurrent = Contribution::where('member_id', $member->id)
            ->where('period_covered', $currentPeriod)
            ->where('amount', '>', 0)
            ->where(function ($q) {
                $q->whereNull('chapa_status')
                  ->orWhere('chapa_status', ChapaStatus::Verified->value);
            })
            ->exists();

        if ($hasPaidCurrent && $member->status === MemberStatus::InArrears) {
            $member->update(['status' => MemberStatus::Active]);
            Log::info('Ledger: Member arrears status cleared to active', ['member_id' => $member->id]);
        }
    }
}
