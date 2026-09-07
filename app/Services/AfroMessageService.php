<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AfroMessageService
{
    protected string $baseUrl;

    protected ?string $token;

    protected ?string $senderId;

    public function __construct()
    {
        $this->baseUrl = config('services.afromessage.base_url', 'https://api.afromessage.com/api');
        $this->token = config('services.afromessage.token');
        $this->senderId = config('services.afromessage.sender_id');
    }

    /**
     * Send SMS via AfroMessage API.
     */
    public function sendSms(string $to, string $message): array
    {
        $normalizedPhone = $this->normalizePhone($to);

        if (empty($this->token)) {
            Log::info("AfroMessage SMS (Mock): to={$normalizedPhone}, msg={$message}");

            return [
                'acknowledge' => 'success',
                'response' => [
                    'status' => 'Mock SMS sent successfully',
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

        $response = Http::withToken($this->token)
            ->acceptJson()
            ->post("{$this->baseUrl}/send", $payload);

        return $response->json();
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
