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

class ProcessMpesaWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [30, 60, 120];

    public function __construct(public array $payload) {}

    public function handle(): void
    {
        // M-Pesa callback body structure:
        // Body.stkCallback.ResultCode (0 = success)
        // Body.stkCallback.CallbackMetadata.Item[] contains Amount, MpesaReceiptNumber, PhoneNumber
        // Body.stkCallback.CheckoutRequestID

        $callback = $this->payload['Body']['stkCallback'] ?? null;
        $resultCode = $callback['ResultCode'] ?? -1;
        $accountRef = null;

        // Extract AccountReference from metadata items
        foreach ($callback['CallbackMetadata']['Item'] ?? [] as $item) {
            // Note: AccountReference is not in callback metadata on M-Pesa ET
            // We stored the txRef in AccountReference when initiating; find contribution by checkout_request_id or by most recent pending
        }

        $checkoutRequestId = $callback['CheckoutRequestID'] ?? null;

        if (! $checkoutRequestId) {
            Log::warning('ProcessMpesaWebhookJob: no CheckoutRequestID in callback');

            return;
        }

        $contribution = Contribution::where('gateway_reference', $checkoutRequestId)
            ->where('method', PaymentMethod::Mpesa)
            ->where('chapa_status', ChapaStatus::Pending)
            ->first();

        if (! $contribution) {
            // Fallback: most-recent-pending if gateway_reference not matched (legacy compatibility)
            Log::warning('ProcessMpesaWebhookJob: no match by gateway_reference, trying fallback', ['checkout_id' => $checkoutRequestId]);
            $contribution = Contribution::where('method', PaymentMethod::Mpesa)
                ->where('chapa_status', ChapaStatus::Pending)
                ->orderByDesc('created_at')
                ->first();
        }

        if (! $contribution) {
            Log::warning('ProcessMpesaWebhookJob: no pending M-Pesa contribution found', $this->payload);

            return;
        }

        if ((int) $resultCode === 0) {
            $contribution->update(['chapa_status' => ChapaStatus::Verified]);
            Log::info('ProcessMpesaWebhookJob: payment confirmed', ['contribution_id' => $contribution->id]);

            SendNotificationJob::dispatch(
                $contribution->idir_id,
                $contribution->member_id,
                NotificationType::PaymentConfirmation,
                ['amount' => $contribution->amount, 'period' => $contribution->period_covered]
            );
        } else {
            $contribution->update(['chapa_status' => ChapaStatus::Failed]);
            Log::warning('ProcessMpesaWebhookJob: payment failed', ['result_code' => $resultCode, 'payload' => $this->payload]);
        }
    }
}
