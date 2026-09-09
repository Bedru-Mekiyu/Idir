<?php

namespace App\Console\Commands;

use App\Enums\ChapaStatus;
use App\Enums\MemberStatus;
use App\Enums\NotificationType;
use App\Jobs\SendNotificationJob;
use App\Models\Contribution;
use App\Models\Member;
use Illuminate\Console\Command;

class SendDueRemindersCommand extends Command
{
    protected $signature = 'idir:send-due-reminders';

    protected $description = 'Send dues reminders to active members who have not yet paid the current period';

    public function handle(): int
    {
        $currentPeriod = now()->format('Y-m');

        $members = Member::with('idir.settings')
            ->where('status', MemberStatus::Active)
            ->get();

        $sent = 0;

        foreach ($members as $member) {
            if (! $member->idir || ! $member->idir->isActive()) {
                continue;
            }

            $hasPaid = Contribution::where('member_id', $member->id)
                ->where('period_covered', $currentPeriod)
                ->where('amount', '>', 0)
                ->where(function ($q) {
                    $q->whereNull('chapa_status')
                        ->orWhere('chapa_status', ChapaStatus::Verified->value);
                })
                ->exists();

            if ($hasPaid) {
                continue;
            }

            SendNotificationJob::dispatch(
                $member->idir_id,
                $member->id,
                NotificationType::DueReminder,
                [
                    'period' => $currentPeriod,
                    'amount' => $member->idir->settings->dues_amount ?? 0,
                ],
            );

            $sent++;
        }

        $this->info("Dispatched {$sent} due reminder(s) for {$currentPeriod}.");

        return self::SUCCESS;
    }
}
