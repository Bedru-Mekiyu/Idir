<?php

namespace App\Policies;

use App\Models\Idir;
use App\Models\Member;
use App\Models\User;
use Filament\Facades\Filament;

class MemberPolicy
{
    /**
     * Check if user is a committee member in the member's idir or current tenant.
     */
    protected function getMemberForContext(User $user, ?Member $member = null): ?Member
    {
        if ($user->is_platform_owner) {
            return null;
        }

        $tenant = Filament::getTenant();
        $idirId = $member?->idir_id ?? ($tenant instanceof Idir ? $tenant->id : null);

        if ($idirId) {
            $matched = $user->members()->where('idir_id', $idirId)->first();
            if ($matched) {
                return $matched;
            }
        }

        return $user->member;
    }

    protected function isCommittee(User $user, ?Member $member = null): bool
    {
        if ($user->is_platform_owner) {
            return false;
        }

        $userMember = $this->getMemberForContext($user, $member);
        if (! $userMember || ! $userMember->isCommitteeMember()) {
            return false;
        }

        if ($member && $userMember->idir_id !== $member->idir_id) {
            return false;
        }

        return true;
    }

    public function viewAny(User $user): bool
    {
        return $this->isCommittee($user);
    }

    public function view(User $user, Member $member): bool
    {
        if ($user->is_platform_owner) {
            return false;
        }

        return $this->isCommittee($user, $member) || ($user->id === $member->user_id);
    }

    public function create(User $user): bool
    {
        return $this->isCommittee($user);
    }

    public function update(User $user, Member $member): bool
    {
        return $this->isCommittee($user, $member);
    }

    public function changeRole(User $user, Member $member): bool
    {
        if ($user->is_platform_owner) {
            return false;
        }

        $userMember = $this->getMemberForContext($user, $member);

        return $userMember && $userMember->isChair() && $userMember->idir_id === $member->idir_id;
    }

    public function handoverChair(User $user, Member $member): bool
    {
        if ($user->is_platform_owner) {
            return false;
        }

        $userMember = $this->getMemberForContext($user, $member);

        return $userMember && $userMember->isChair() && $userMember->idir_id === $member->idir_id && $userMember->id !== $member->id;
    }

    public function deactivate(User $user, Member $member): bool
    {
        if ($user->is_platform_owner) {
            return false;
        }

        $userMember = $this->getMemberForContext($user, $member);

        return $userMember && $userMember->isChair() && $userMember->idir_id === $member->idir_id;
    }

    public function reactivate(User $user, Member $member): bool
    {
        return $this->isCommittee($user, $member);
    }

    public function delete(User $user, Member $member): bool
    {
        // Platform owner can never delete idir members.
        // Physical deletion is disabled to preserve immutable ledger and claims records;
        // members should be deactivated instead.
        return false;
    }

    public function warn(User $user, Member $member): bool
    {
        return $this->isCommittee($user, $member);
    }

    public function exclude(User $user, Member $member): bool
    {
        if ($user->is_platform_owner) {
            return false;
        }

        $userMember = $this->getMemberForContext($user, $member);

        return $userMember
            && ($userMember->isChair() || $userMember->isSecretary())
            && $userMember->idir_id === $member->idir_id
            && ! is_null($member->exclusion_warning_sent_at);
    }
}
