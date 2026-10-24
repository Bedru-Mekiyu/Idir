<?php

namespace App\Services\Sms\Drivers;

use App\Services\Sms\Concerns\NormalizesEthiopianPhone;
use App\Services\Sms\SmsGatewayInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * GeezSMS Ethiopian SMS Driver.
 *
 * API: POST https://api.geezsms.com/api/v1/sms/send/bulk
 * Auth: token in JSON body (not Bearer header)
 *
 * Required .env keys:
 *   GEEZSMS_TOKEN     — API token from geezsms.com account → API section
 *   GEEZSMS_SENDER_ID — Optional sender name (request from GeezSMS support)
 */
class GeezSmsDriver implements SmsGatewayInterface
{
    use NormalizesEthiopianPhone;

    protected ?string $token;

    protected ?string $senderId;

    protected ?string $shortcodeId;

    public function __construct()
    {
        $this->token = config('services.geezsms.token');
        $this->senderId = config('services.geezsms.sender_id');
        $this->shortcodeId = config('services.geezsms.shortcode_id');
    }

    public function sendSms(string $to, string $message): array
    {
        $normalizedPhone = $this->normalizePhone($to);

        if (empty($this->token)) {
            Log::warning("GeezSmsDriver: GEEZSMS_TOKEN not configured. SMS to {$normalizedPhone} was NOT sent.");

            return ['acknowledge' => 'error', 'configured' => false, 'response' => ['errors' => ['GeezSMS token not configured.'], 'to' => $normalizedPhone]];
        }

        $payload = [
            'token' => $this->token,
            'contacts' => [['phone_number' => ltrim($normalizedPhone, '+')]], // GeezSMS expects without leading +
            'msg' => $message,
        ];

        if ($this->senderId) {
            $payload['sender_id'] = $this->senderId;
        }

        if ($this->shortcodeId) {
            $payload['shortcode_id'] = $this->shortcodeId;
        }

        try {
            $response = Http::acceptJson()->timeout(15)->post('https://api.geezsms.com/api/v1/sms/send/bulk', $payload);
            $json = $response->json();

            // GeezSMS returns {"error": false} on success or {"status": 200, "message": "success"}
            $hasError = is_array($json) && isset($json['error']) && $json['error'] === true;
            $isSuccess = $response->successful() && ! $hasError && (
                ($json['error'] ?? null) === false ||
                ($json['status'] ?? 0) === 200 ||
                ($json['status'] ?? '') === 'success'
            );

            if (! $isSuccess) {
                $errorMsg = $json['msg'] ?? $json['message'] ?? 'Unknown error';
                Log::warning("GeezSmsDriver: SMS delivery unsuccessful ({$errorMsg}). Note: If 'No Shortcode was found', ensure a shortcode/sender is active in your GeezSMS dashboard.");
            }

            return [
                'acknowledge' => $isSuccess ? 'success' : 'error',
                'configured' => true,
                'response' => $json ?? ['raw' => $response->body()],
            ];
        } catch (\Throwable $e) {
            Log::error("GeezSmsDriver: request failed: {$e->getMessage()}");

            return ['acknowledge' => 'error', 'configured' => true, 'response' => ['errors' => [$e->getMessage()]]];
        }
    }
}
