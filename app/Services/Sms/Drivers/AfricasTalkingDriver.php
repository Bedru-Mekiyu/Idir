<?php

namespace App\Services\Sms\Drivers;

use AfricasTalking\SDK\AfricasTalking;
use App\Services\Sms\Concerns\NormalizesEthiopianPhone;
use App\Services\Sms\SmsGatewayInterface;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

/**
 * Africa's Talking SMS Driver.
 *
 * Documentation: https://developers.africastalking.com/docs/
 * PHP SDK: composer require africastalking/africastalking
 *
 * Required .env keys:
 *   AT_USERNAME  — Your AT app username. Use 'sandbox' for testing (no approval needed).
 *   AT_API_KEY   — Generated in AT dashboard → Settings → API Key
 *   AT_SENDER_ID — Optional. Custom alphanumeric sender ID (request from AT support for production).
 *
 * Sandbox: whitelist test phone numbers in your AT sandbox dashboard before testing.
 * Register at: https://africastalking.com
 */
class AfricasTalkingDriver implements SmsGatewayInterface
{
    use NormalizesEthiopianPhone;

    protected ?string $username;

    protected ?string $apiKey;

    protected ?string $senderId;

    protected ?string $shortcode;

    public function __construct()
    {
        $this->username = config('services.africastalking.username', 'sandbox');
        $this->apiKey = config('services.africastalking.api_key');
        $this->senderId = config('services.africastalking.sender_id');
        $this->shortcode = config('services.africastalking.shortcode');
    }

    public function sendSms(string $to, string $message): array
    {
        $normalizedPhone = $this->normalizePhone($to);

        if (empty($this->apiKey)) {
            Log::warning("AfricasTalkingDriver: AT_API_KEY not configured. SMS to {$normalizedPhone} was NOT sent.");

            return [
                'acknowledge' => 'error',
                'configured' => false,
                'response' => [
                    'errors' => ['Africa\'s Talking API key not configured (AT_API_KEY).'],
                    'to' => $normalizedPhone,
                ],
            ];
        }

        try {
            $AT = new AfricasTalking($this->username, $this->apiKey);
            $sms = $AT->sms();

            $params = [
                'to' => $normalizedPhone,
                'message' => $message,
            ];

            $from = $this->shortcode ?: $this->senderId;
            if ($from) {
                $params['from'] = $from;
            }

            $result = $sms->send($params);

            // AT SDK returns an object with getRecipients(), getStatus(), etc.
            // or an array depending on version. Normalize it.
            $recipients = [];
            if (isset($result['data']->SMSMessageData->Recipients)) {
                $recipients = json_decode(json_encode($result['data']->SMSMessageData->Recipients), true);
            } elseif (isset($result['SMSMessageData']['Recipients'])) {
                $recipients = $result['SMSMessageData']['Recipients'];
            }

            $isSuccess = count(array_filter($recipients, fn ($r) => ($r['status'] ?? '') === 'Success')) > 0;

            return [
                'acknowledge' => $isSuccess ? 'success' : 'error',
                'configured' => true,
                'response' => json_decode(json_encode($result), true) ?? [],
            ];
        } catch (\Throwable $e) {
            $rawErrorBody = null;
            if ($e instanceof RequestException && $e->hasResponse()) {
                $rawErrorBody = (string) $e->getResponse()->getBody();
            }

            Log::error("AfricasTalkingDriver: SDK error: {$e->getMessage()}", [
                'raw_body' => $rawErrorBody,
            ]);

            return [
                'acknowledge' => 'error',
                'configured' => ! str_contains($e->getMessage(), '401 Unauthorized'),
                'response' => [
                    'errors' => [$e->getMessage()],
                    'raw_response' => $rawErrorBody,
                ],
            ];
        }
    }
}
