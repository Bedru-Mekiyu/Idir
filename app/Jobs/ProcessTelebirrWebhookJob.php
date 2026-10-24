<?php

namespace App\Jobs;

use App\Enums\ChapaStatus;
use App\Enums\NotificationType;
use App\Enums\PaymentMethod;
use App\Models\Contribution;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessTelebirrWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [30, 60, 120];

    public function __construct(public array $payload) {}

    public function handle(): void
    {
        // Telebirr H5 webhook payload shape:
        // { "trade_status": "Payment successful", "merch_order_id": "IDIR-TB-xxx", ... }
        // OR: { "out_trade_no": "IDIR-TB-xxx", "trade_status": "SUCCESS" }
        // We store our txRef as merch_order_id — match by it.

        $txRef = $this->payload['merch_order_id']
            ?? $this->payload['out_trade_no']
            ?? $this->payload['trade_no']
            ?? null;

        $status = strtolower((string) ($this->payload['trade_status'] ?? ''));
        $isSuccess = str_contains($status, 'success') || str_contains($status, 'paid');

        if (! $txRef) {
            Log::warning('ProcessTelebirrWebhookJob: no txRef in payload', $this->payload);

            return;
        }

        $contribution = Contribution::where('chapa_tx_ref', $txRef)
            ->where('method', PaymentMethod::Telebirr)
            ->where('chapa_status', ChapaStatus::Pending)
            ->first();

        if (! $contribution) {
            Log::warning('ProcessTelebirrWebhookJob: no matching pending Telebirr contribution', ['tx_ref' => $txRef]);

            return;
        }

        if ($isSuccess) {
            $contribution->update(['chapa_status' => ChapaStatus::Verified]);
            Log::info('ProcessTelebirrWebhookJob: payment confirmed', ['contribution_id' => $contribution->id, 'tx_ref' => $txRef]);

            SendNotificationJob::dispatch(
                $contribution->idir_id,
                $contribution->member_id,
                NotificationType::PaymentConfirmation,
                ['amount' => $contribution->amount, 'period' => $contribution->period_covered]
            );
        } else {
            $contribution->update(['chapa_status' => ChapaStatus::Failed]);
            Log::warning('ProcessTelebirrWebhookJob: payment not successful', ['status' => $status, 'tx_ref' => $txRef]);
        }
    }
}
