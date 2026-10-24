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

/**
 * Ethio Telecom Telebirr H5 Payment Driver.
 *
 * Implements the Telebirr Fabric H5 payment flow:
 * 1. POST /payment/v1/auth/authToken → Fabric access token
 * 2. POST order to Telebirr → get toPayUrl (H5 checkout URL)
 * 3. Redirect member to toPayUrl → they pay in browser
 * 4. Telebirr POSTs async webhook to /api/telebirr/webhook
 *
 * This is a REDIRECT flow (like Chapa hosted checkout), NOT a USSD push.
 * For USSD push (Telebirr via Chapa), see ChapaDriver::initializeDirectCharge(type='telebirr').
 *
 * Required .env keys:
 *   TELEBIRR_APP_ID      — Fabric App ID (from developer.ethiotelecom.et)
 *   TELEBIRR_APP_KEY     — Fabric App Key / Secret
 *   TELEBIRR_SHORT_CODE  — Merchant short code
 *   TELEBIRR_PUBLIC_KEY  — Telebirr RSA public key (for payload encryption; from portal docs)
 *   TELEBIRR_BASE_URL    — Default: https://app.ethiomobilemoney.et:2121/ammapi/payment/service-openup
 *
 * Register at: https://developer.ethiotelecom.et
 * Business license + TIN required for production; sandbox available without approval.
 */
class TelebirrDriver implements PaymentGatewayInterface
{
    protected string $baseUrl;

    protected ?string $appId;

    protected ?string $appKey;

    protected ?string $shortCode;

    protected ?string $publicKey; // Telebirr's RSA public key for AES key encryption

    public function __construct()
    {
        $this->baseUrl = config('services.telebirr.base_url', 'https://app.ethiomobilemoney.et:2121/ammapi/payment/service-openup');
        $this->appId = config('services.telebirr.app_id');
        $this->appKey = config('services.telebirr.app_key');
        $this->shortCode = config('services.telebirr.short_code');
        $this->publicKey = config('services.telebirr.public_key');
    }

    public function initialize(Member $member, float $amount, string $period, ?string $callbackUrl = null, ?string $returnUrl = null): array
    {
        if (empty($this->appId) || empty($this->appKey) || empty($this->shortCode)) {
            Log::warning('TelebirrDriver: missing credentials — returning unconfigured response.');

            return ['status' => 'error', 'configured' => false, 'message' => 'Telebirr credentials not configured (TELEBIRR_APP_ID, TELEBIRR_APP_KEY, TELEBIRR_SHORT_CODE).'];
        }

        $txRef = 'IDIR-TB-'.$member->idir_id.'-'.$member->id.'-'.Str::uuid();
        $nonce = Str::random(32);
        $timestamp = (string) (time() * 1000);

        $this->createPendingContribution($member, $amount, $period, $txRef);

        $rawObj = [
            'appId' => $this->appId,
            'merch_order_id' => $txRef,
            'nonce' => $nonce,
            'notify_url' => $callbackUrl ?? url('/api/telebirr/webhook'),
            'return_url' => $returnUrl ?? url('/payment/success'),
            'subject' => 'Idir Contribution '.$period,
            'timeout_express' => '30',
            'timestamp' => $timestamp,
            'total_amount' => number_format($amount, 2, '.', ''),
            'trade_type' => 'Payment',
        ];

        try {
            // Build the signed/encrypted rawRequest per Telebirr H5 spec
            $rawRequest = $this->buildRawRequest($rawObj);

            $response = Http::timeout(15)
                ->acceptJson()
                ->post($this->baseUrl, [
                    'appid' => $this->appId,
                    'sign' => $this->sign($rawObj),
                    'timestamp' => $timestamp,
                    'nonce' => $nonce,
                    'rawRequest' => $rawRequest,
                ]);

            $json = $response->json() ?? [];

            if ($response->failed() || ($json['code'] ?? '') !== '0') {
                Log::warning('TelebirrDriver: order creation failed', ['response' => $json]);

                return ['status' => 'error', 'message' => $json['message'] ?? 'Telebirr order creation failed', 'data' => $json];
            }

            $toPayUrl = $json['data']['toPayUrl'] ?? $json['data']['payInfo'] ?? null;

            return [
                'status' => 'success',
                'data' => [
                    'checkout_url' => $toPayUrl,
                    'tx_ref' => $txRef,
                ],
            ];
        } catch (\Throwable $e) {
            Log::error('TelebirrDriver: network error', ['error' => $e->getMessage()]);

            return ['status' => 'error', 'message' => 'Telebirr unreachable: '.$e->getMessage()];
        }
    }

    public function verify(string $txRef): array
    {
        // Telebirr verification is webhook-driven. This method is called by ProcessPaymentWebhookJob
        // when we receive a Telebirr webhook. The webhook body itself is the verification.
        // A direct query endpoint may be available; return pending for now.
        return ['status' => 'success', 'data' => ['status' => 'pending', 'tx_ref' => $txRef]];
    }

    /**
     * Build the rawRequest: JSON-encode the raw object, then encrypt with AES,
     * then encrypt the AES key with Telebirr's RSA public key, then combine.
     * If public key is not configured, falls back to base64(JSON) for sandbox testing.
     */
    protected function buildRawRequest(array $rawObj): string
    {
        $json = json_encode($rawObj, JSON_UNESCAPED_UNICODE);

        if (empty($this->publicKey)) {
            // Sandbox mode: no RSA encryption, just base64 encode
            return base64_encode($json);
        }

        // Production: AES-128-CBC encrypt the JSON, then RSA-encrypt the AES key
        $aesKey = random_bytes(16);
        $iv = random_bytes(16);
        $encrypted = openssl_encrypt($json, 'AES-128-CBC', $aesKey, 0, $iv);

        $publicKeyPem = $this->formatPublicKey($this->publicKey);
        openssl_public_encrypt($aesKey, $encryptedKey, $publicKeyPem, OPENSSL_PKCS1_OAEP_PADDING);

        return base64_encode($encryptedKey).':'.base64_encode($iv).':'.$encrypted;
    }

    /**
     * Sign the sorted key=value string with HMAC-SHA256 using appKey.
     * (Telebirr H5 uses HMAC-SHA256 for signature, not RSA)
     */
    protected function sign(array $payload): string
    {
        ksort($payload);
        $parts = [];
        foreach ($payload as $key => $value) {
            if ($value !== null && $value !== '') {
                $parts[] = "{$key}={$value}";
            }
        }
        $stringToSign = implode('&', $parts);

        return strtoupper(hash_hmac('sha256', $stringToSign, $this->appKey));
    }

    protected function formatPublicKey(string $key): string
    {
        if (str_contains($key, '-----BEGIN PUBLIC KEY-----')) {
            return $key;
        }
        $key = wordwrap($key, 64, "\n", true);

        return "-----BEGIN PUBLIC KEY-----\n{$key}\n-----END PUBLIC KEY-----";
    }

    protected function createPendingContribution(Member $member, float $amount, string $period, string $txRef): Contribution
    {
        return Contribution::create([
            'idir_id' => $member->idir_id,
            'member_id' => $member->id,
            'amount' => $amount,
            'method' => PaymentMethod::Telebirr,
            'type' => ContributionType::Cash,
            'period_covered' => $period,
            'chapa_tx_ref' => $txRef,
            'chapa_status' => ChapaStatus::Pending,
            'is_correction' => false,
            'notes' => 'Telebirr H5 Direct — '.$period,
        ]);
    }
}
