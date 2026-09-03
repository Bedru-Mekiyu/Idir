<?php

namespace App\Policies;

use App\Models\Contribution;
use App\Models\User;

class ContributionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->member?->isCommitteeMember() ?? false;
    }

    public function view(User $user, Contribution $contribution): bool
    {
        return ($user->member?->isCommitteeMember() ?? false) 
            || ($user->member?->id === $contribution->member_id);
    }

    public function create(User $user): bool
    {
        // Recording cash contributions is restricted to Treasurer and Chair
        return $user->member?->isTreasurer() || $user->member?->isChair();
    }

    public function correct(User $user, Contribution $contribution): bool
    {
        // Offsetting corrections restricted to Treasurer and Chair
        return ($user->member?->isTreasurer() || $user->member?->isChair()) 
            && !$contribution->is_correction;
    }

    public function update(User $user, Contribution $contribution): bool
    {
        // Immutable ledger: NO direct updates allowed ever
        return false;
    }

    public function delete(User $user, Contribution $contribution): bool
    {
        // Immutable ledger: NO direct deletions allowed ever
        return false;
    }
}
