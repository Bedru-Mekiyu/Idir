<?php

namespace App\Jobs;

use App\Enums\ChapaStatus;
use App\Models\Contribution;
use App\Services\ChapaService;
use App\Services\LedgerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessChapaWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;
    public array $backoff = [10, 30, 60, 120, 300];

    public function __construct(
        public string $txRef,
        public array $payload = []
    ) {}

    public function handle(ChapaService $chapa, LedgerService $ledger): void
    {
        $verification = $chapa->verifyPayment($this->txRef);
        $contribution = Contribution::where('chapa_tx_ref', $this->txRef)->first();

        if (!$contribution) {
            Log::warning("Chapa Webhook: Contribution with tx_ref {$this->txRef} not found.");
            return;
        }

        $isSuccess = ($verification['status'] ?? '') === 'success' 
            && (($verification['data']['status'] ?? '') === 'success');

        if ($isSuccess) {
            $contribution->update([
                'chapa_status' => ChapaStatus::Verified,
            ]);

            $ledger->recalculateFundBalance($contribution->idir_id);
            $ledger->updateMemberArrearsStatus($contribution->member_id);

            // Trigger notification job
            SendNotificationJob::dispatch(
                $contribution->idir_id,
                $contribution->member_id,
                \App\Enums\NotificationType::PaymentConfirmation,
                [
                    'amount' => $contribution->amount,
                    'period' => $contribution->period_covered,
                ]
            );
        } else {
            $contribution->update([
                'chapa_status' => ChapaStatus::Failed,
            ]);
        }
    }
}
