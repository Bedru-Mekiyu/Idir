<?php

namespace App\Jobs;

use App\Enums\ChapaStatus;
use App\Enums\NotificationType;
use App\Models\Contribution;
use App\Services\LedgerService;
use App\Services\Payments\PaymentGatewayManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessPaymentWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    public array $backoff = [10, 30, 60, 120, 300];

    public function __construct(
        public string $gateway,
        public string $txRef,
        public array $payload = []
    ) {}

    public function handle(PaymentGatewayManager $gatewayManager, LedgerService $ledger): void
    {
        $driver = $gatewayManager->driver($this->gateway);
        $verification = $driver->verify($this->txRef);

        DB::transaction(function () use ($verification, $ledger) {
            $contribution = Contribution::where('chapa_tx_ref', $this->txRef)->lockForUpdate()->first();

            if (! $contribution) {
                Log::warning("Webhook: Contribution with tx_ref {$this->txRef} not found.");
                return;
            }

            if ($contribution->chapa_status === ChapaStatus::Verified) {
                Log::info("Webhook: Contribution with tx_ref {$this->txRef} already verified.");
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

                SendNotificationJob::dispatch(
                    $contribution->idir_id,
                    $contribution->member_id,
                    NotificationType::PaymentConfirmation,
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
        });
    }
}
