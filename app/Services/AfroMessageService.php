<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AfroMessageService
{
    protected string $baseUrl;

    protected ?string $token;

    protected ?string $senderId;

    protected ?string $identifierId;

    public function __construct()
    {
        $this->baseUrl = config('services.afromessage.base_url', 'https://api.afromessage.com/api');
        $this->token = config('services.afromessage.token');
        $this->senderId = config('services.afromessage.sender_id');
        $this->identifierId = config('services.afromessage.identifier_id');
    }

    /**
     * Send SMS via AfroMessage API.
     */
    public function sendSms(string $to, string $message): array
    {
        $normalizedPhone = $this->normalizePhone($to);

        // Honest behaviour: with no real credentials we do NOT fabricate a
        // success response. The caller records this as a FAILED NotificationEvent
        // with a clear reason, rather than a misleading "sent" record.
        if (empty($this->token)) {
            Log::warning("AfroMessage not configured (missing AFROMESSAGE_TOKEN): SMS to {$normalizedPhone} was NOT sent.");

            return [
                'acknowledge' => 'error',
                'configured' => false,
                'response' => [
                    'errors' => ['AfroMessage credentials are not configured (missing AFROMESSAGE_TOKEN); no SMS was sent.'],
                    'to' => $normalizedPhone,
                ],
            ];
        }

        $payload = [
            'to' => $normalizedPhone,
            'message' => $message,
        ];

        if ($this->senderId) {
            $payload['sender'] = $this->senderId;
        }

        if ($this->identifierId) {
            $payload['from'] = $this->identifierId;
        }

        try {
            $response = Http::withToken($this->token)
                ->acceptJson()
                ->timeout(15)
                ->post("{$this->baseUrl}/send", $payload);
        } catch (\Throwable $e) {
            Log::error("AfroMessage SMS request threw: {$e->getMessage()}");

            return [
                'acknowledge' => 'error',
                'configured' => true,
                'response' => ['errors' => [$e->getMessage()]],
            ];
        }

        $json = $response->json();

        if (! is_array($json)) {
            return [
                'acknowledge' => 'error',
                'configured' => true,
                'http_status' => $response->status(),
                'response' => ['raw' => $response->body()],
            ];
        }

        return $json;
    }

    /**
     * Normalize Ethiopian phone number format to international (+251...).
     */
    public function normalizePhone(string $phone): string
    {
        $cleaned = preg_replace('/[^0-9]/', '', $phone);

        if (str_starts_with($cleaned, '09') || str_starts_with($cleaned, '07')) {
            return '+251'.substr($cleaned, 1);
        }

        if (str_starts_with($cleaned, '251')) {
            return '+'.$cleaned;
        }

        if (strlen($cleaned) === 9 && ($cleaned[0] === '9' || $cleaned[0] === '7')) {
            return '+251'.$cleaned;
        }

        return '+'.$cleaned;
    }
}
