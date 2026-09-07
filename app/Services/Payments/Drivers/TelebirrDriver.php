<?php

namespace App\Services\Payments\Drivers;

use App\Enums\ChapaStatus;
use App\Enums\ContributionType;
use App\Enums\PaymentMethod;
use App\Models\Contribution;
use App\Models\Member;
use App\Services\Payments\PaymentGatewayInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TelebirrDriver implements PaymentGatewayInterface
{
    protected string $baseUrl;
    protected string $appId;
    protected string $appKey;
    protected string $shortCode;
    protected string $publicKey;
    protected string $privateKey;

    public function __construct()
    {
        // Example configuration endpoints for Telebirr API
        $this->baseUrl = config('services.telebirr.base_url', 'https://app.ethiomobilemoney.et:2121/api/access/send'); // Example URL
        $this->appId = config('services.telebirr.app_id', '');
        $this->appKey = config('services.telebirr.app_key', '');
        $this->shortCode = config('services.telebirr.short_code', '');
        $this->publicKey = config('services.telebirr.public_key', '');
        $this->privateKey = config('services.telebirr.private_key', '');
    }

    public function initialize(Member $member, float $amount, string $period, ?string $callbackUrl = null, ?string $returnUrl = null): array
    {
        if (empty($this->appId) || empty($this->privateKey)) {
            throw new \Exception('Telebirr driver is not fully configured. Missing App ID or Private Key in .env');
        }

        $txRef = 'IDIR-'.$member->idir_id.'-'.$member->id.'-'.Str::uuid();

        // Lock the transaction in the ledger as Pending
        Contribution::create([
            'idir_id' => $member->idir_id,
            'member_id' => $member->id,
            'amount' => $amount,
            'method' => PaymentMethod::Chapa, // Assuming we want a specific Telebirr enum or fallback
            'type' => ContributionType::Cash,
            'period_covered' => $period,
            'chapa_tx_ref' => $txRef,
            'chapa_status' => ChapaStatus::Pending,
            'is_correction' => false,
            'notes' => __('contribution.period_covered').': '.$period . ' (Telebirr)',
        ]);

        $payload = $this->buildPayload($member, $amount, $txRef, $callbackUrl, $returnUrl);
        $signature = $this->signPayload($payload);

        // Standard Telebirr Request wrapper
        $requestData = [
            'appid' => $this->appId,
            'sign' => $signature,
            'ussd' => $payload, // Telebirr often requires the encrypted or raw payload here
        ];

        try {
            $response = Http::timeout(15)
                ->acceptJson()
                ->post($this->baseUrl, $requestData);

            if ($response->failed()) {
                Log::error('Telebirr initialization failed', ['response' => $response->body()]);
                throw new \Exception('Telebirr API Error: ' . $response->status());
            }

            $json = $response->json();
            
            // Expected response format translates to standard platform return
            return [
                'status' => 'success',
                'data' => [
                    'checkout_url' => $json['data']['toPayUrl'] ?? '', // Adjust to match exact Telebirr API response key
                    'tx_ref' => $txRef,
                ],
            ];
        } catch (\Exception $e) {
            Log::error('Telebirr Network Error', ['msg' => $e->getMessage()]);
            throw clone $e;
        }
    }

    public function verify(string $txRef): array
    {
        if (empty($this->appId) || empty($this->privateKey)) {
            throw new \Exception('Telebirr driver is not configured.');
        }

        // Telebirr typically relies strictly on the Webhook for verification.
        // If a query endpoint exists, it follows the same RSA signing pattern.
        $payload = [
            'appId' => $this->appId,
            'outTradeNo' => $txRef,
            'nonce' => Str::random(32),
            'timestamp' => (string) (time() * 1000),
        ];

        $signature = $this->signPayload($payload);

        $requestData = [
            'appid' => $this->appId,
            'sign' => $signature,
            'ussd' => $payload,
        ];

        $response = Http::timeout(10)->post("{$this->baseUrl}/query", $requestData);

        return [
            'status' => 'success',
            'data' => [
                'status' => $response->json('data.tradeStatus') === 'COMPLETED' ? 'success' : 'failed',
                'tx_ref' => $txRef,
            ]
        ];
    }

    /**
     * Builds the standard sorted array required for Telebirr.
     */
    protected function buildPayload(Member $member, float $amount, string $txRef, ?string $callbackUrl, ?string $returnUrl): array
    {
        return [
            'appId' => $this->appId,
            'appKey' => $this->appKey,
            'nonce' => Str::random(32),
            'notifyUrl' => $callbackUrl ?? url('/api/telebirr/webhook'),
            'outTradeNo' => $txRef,
            'receiveName' => $member->idir->name ?? 'Idir',
            'returnUrl' => $returnUrl ?? url('/payment/success'),
            'shortCode' => $this->shortCode,
            'subject' => 'Idir Contribution',
            'timeoutExpress' => '120',
            'timestamp' => (string) (time() * 1000),
            'totalAmount' => (string) $amount,
        ];
    }

    /**
     * Telebirr requires SHA256WithRSA signing on an alphabetically sorted key=value string.
     */
    protected function signPayload(array $payload): string
    {
        ksort($payload);
        $stringToSign = '';
        foreach ($payload as $key => $value) {
            if ($value !== null && $value !== '') {
                $stringToSign .= "{$key}={$value}&";
            }
        }
        $stringToSign = rtrim($stringToSign, '&');

        $privateKeyResource = openssl_pkey_get_private($this->formatPrivateKey($this->privateKey));
        
        if (!$privateKeyResource) {
            throw new \Exception("Invalid Telebirr RSA Private Key format.");
        }

        openssl_sign($stringToSign, $signature, $privateKeyResource, OPENSSL_ALGO_SHA256);
        return base64_encode($signature);
    }

    /**
     * Helper to ensure the private key has proper headers.
     */
    protected function formatPrivateKey(string $key): string
    {
        if (!str_contains($key, '-----BEGIN PRIVATE KEY-----')) {
            $key = wordwrap($key, 64, "\n", true);
            return "-----BEGIN PRIVATE KEY-----\n{$key}\n-----END PRIVATE KEY-----";
        }
        return $key;
    }
}
