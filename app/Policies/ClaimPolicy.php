<?php

namespace App\Policies;

use App\Enums\ClaimStatus;
use App\Models\Claim;
use App\Models\User;

class ClaimPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->member?->isCommitteeMember() ?? false;
    }

    public function view(User $user, Claim $claim): bool
    {
        return ($user->member?->isCommitteeMember() ?? false) 
            || ($user->member?->id === $claim->member_id);
    }

    public function create(User $user): bool
    {
        // Any active member who meets vesting can file
        return !is_null($user->member);
    }

    public function approve(User $user, Claim $claim): bool
    {
        // Must be a committee member and claim must be in review
        return ($user->member?->isCommitteeMember() ?? false)
            && in_array($claim->status, [ClaimStatus::Pending, ClaimStatus::UnderReview]);
    }

    public function reject(User $user, Claim $claim): bool
    {
        // Must be a committee member and claim must be in review
        return ($user->member?->isCommitteeMember() ?? false)
            && in_array($claim->status, [ClaimStatus::Pending, ClaimStatus::UnderReview]);
    }

    public function disburse(User $user, Claim $claim): bool
    {
        // Recording disbursement restricted to Treasurer and Chair
        return ($user->member?->isTreasurer() || $user->member?->isChair())
            && in_array($claim->status, [ClaimStatus::Approved, ClaimStatus::Paid]);
    }

    public function update(User $user, Claim $claim): bool
    {
        return $user->member?->isCommitteeMember() ?? false;
    }

    public function delete(User $user, Claim $claim): bool
    {
        return false;
    }
}
