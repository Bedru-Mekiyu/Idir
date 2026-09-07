<?php

namespace App\Console\Commands;

use App\Enums\ChapaStatus;
use App\Enums\MemberStatus;
use App\Models\Contribution;
use App\Models\Member;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckArrearsCommand extends Command
{
    protected $signature = 'idir:check-arrears';

    protected $description = 'Check members dues payment status and mark overdue members as in arrears';

    public function handle(): int
    {
        $currentPeriod = now()->format('Y-m');
        $activeMembers = Member::with('idir.settings')
            ->where('status', MemberStatus::Active)
            ->get();

        $updatedCount = 0;

        foreach ($activeMembers as $member) {
            $settings = $member->idir->settings;
            $graceDays = $settings->late_fee_grace_days ?? 7;

            // Check if member has paid for current period
            $hasPaid = Contribution::where('member_id', $member->id)
                ->where('period_covered', $currentPeriod)
                ->where('amount', '>', 0)
                ->where(function ($q) {
                    $q->whereNull('chapa_status')
                        ->orWhere('chapa_status', ChapaStatus::Verified->value);
                })
                ->exists();

            // If day of month is past grace period and hasn't paid, flag in_arrears
            if (! $hasPaid && now()->day > $graceDays) {
                // Check if joined this month
                if ($member->join_date && Carbon::parse($member->join_date)->isCurrentMonth()) {
                    continue;
                }

                $member->update(['status' => MemberStatus::InArrears]);
                $updatedCount++;
            }
        }

        $this->info("Updated {$updatedCount} member(s) to in-arrears status.");

        return self::SUCCESS;
    }
}
