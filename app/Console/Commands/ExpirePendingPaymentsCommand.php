<?php

namespace App\Console\Commands;

use App\Enums\ChapaStatus;
use App\Models\Contribution;
use Illuminate\Console\Command;

class ExpirePendingPaymentsCommand extends Command
{
    protected $signature = 'idir:expire-pending-payments';
    protected $description = 'Expire digital payments that have been in pending state for more than 24 hours';

    public function handle(): int
    {
        $expiryHours = config('idir.chapa_pending_expiry_hours', 24);
        $cutoff = now()->subHours($expiryHours);

        $count = Contribution::where('chapa_status', ChapaStatus::Pending->value)
            ->where('created_at', '<', $cutoff)
            ->update([
                'chapa_status' => ChapaStatus::Expired->value,
            ]);

        $this->info("Expired {$count} pending payment(s) older than {$expiryHours} hours.");

        return self::SUCCESS;
    }
}
