<?php

namespace App\Policies;

use App\Models\IdirSetting;
use App\Models\User;

class IdirSettingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->member?->isCommitteeMember() ?? false;
    }

    public function view(User $user, IdirSetting $setting): bool
    {
        return $user->member?->isCommitteeMember() ?? false;
    }

    public function update(User $user, IdirSetting $setting): bool
    {
        // Updating core idir settings (dues, approval thresholds, triggers) restricted to Chair
        return $user->member?->isChair() ?? false;
    }
}
