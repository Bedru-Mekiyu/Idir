<?php

namespace App\Services\Sms\Drivers;

use App\Services\Sms\Concerns\NormalizesEthiopianPhone;
use App\Services\Sms\SmsGatewayInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AfroMessageDriver implements SmsGatewayInterface
{
    use NormalizesEthiopianPhone;

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

    public function sendSms(string $to, string $message): array
    {
        $normalizedPhone = $this->normalizePhone($to);

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

        // Standardize output to include 'acknowledge' based on response
        if (! isset($json['acknowledge'])) {
            $json['acknowledge'] = $response->successful() ? 'success' : 'error';
            $json['configured'] = true;
        }

        return $json;
    }
}
