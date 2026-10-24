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
 * Safaricom Ethiopia M-Pesa STK Push Payment Driver.
 *
 * Flow:
 * 1. GET /oauth/v1/generate?grant_type=client_credentials (Basic Auth) → access_token
 * 2. POST /mpesa/stkpush/v1/processrequest → USSD prompt on customer phone
 * 3. Customer enters PIN → Safaricom POSTs result to /api/mpesa/webhook
 *
 * Required .env keys:
 *   MPESA_CONSUMER_KEY    — From developer.safaricom.et dashboard
 *   MPESA_CONSUMER_SECRET — From developer.safaricom.et dashboard
 *   MPESA_SHORTCODE       — Business short code (use 174379 for sandbox)
 *   MPESA_PASSKEY         — STK Push passkey (from portal; sandbox passkey available in docs)
 *   MPESA_BASE_URL        — Sandbox: https://sandbox.safaricom.et; Production: https://api.safaricom.et
 *   MPESA_CALLBACK_URL    — Public webhook URL: https://yourdomain.com/api/mpesa/webhook
 *
 * Register at: https://developer.safaricom.et (sandbox available immediately on free signup)
 */
class MpesaDriver implements PaymentGatewayInterface
{
    protected string $baseUrl;

    protected ?string $consumerKey;

    protected ?string $consumerSecret;

    protected ?string $shortcode;

    protected ?string $passkey;

    protected ?string $callbackUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.mpesa.base_url', 'https://sandbox.safaricom.et');
        $this->consumerKey = config('services.mpesa.consumer_key');
        $this->consumerSecret = config('services.mpesa.consumer_secret');
        $this->shortcode = config('services.mpesa.shortcode');
        $this->passkey = config('services.mpesa.passkey');
        $this->callbackUrl = config('services.mpesa.callback_url');
    }

    public function initialize(Member $member, float $amount, string $period, ?string $callbackUrl = null, ?string $returnUrl = null): array
    {
        if (empty($this->consumerKey) || empty($this->consumerSecret) || empty($this->shortcode) || empty($this->passkey)) {
            Log::warning('MpesaDriver: missing credentials — returning unconfigured response.');

            return ['status' => 'error', 'configured' => false, 'message' => 'M-Pesa credentials not configured (MPESA_CONSUMER_KEY, MPESA_CONSUMER_SECRET, MPESA_SHORTCODE, MPESA_PASSKEY).'];
        }

        try {
            $token = $this->getAccessToken();
        } catch (\Throwable $e) {
            Log::error('MpesaDriver: token fetch failed', ['error' => $e->getMessage()]);

            return ['status' => 'error', 'message' => 'M-Pesa auth failed: '.$e->getMessage()];
        }

        $txRef = 'IDIR-MP-'.$member->idir_id.'-'.$member->id.'-'.Str::uuid();
        $timestamp = now()->format('YmdHis');
        $password = $this->generatePassword($timestamp);
        $phone = $this->normalizeMpesaPhone($member->phone);

        // Amount must be integer for M-Pesa Ethiopia
        $mpesaAmount = (int) ceil($amount);

        $pendingContribution = Contribution::create([
            'idir_id' => $member->idir_id,
            'member_id' => $member->id,
            'amount' => $amount,
            'method' => PaymentMethod::Mpesa,
            'type' => ContributionType::Cash,
            'period_covered' => $period,
            'chapa_tx_ref' => $txRef, // reuses the tx_ref column for M-Pesa reference
            'chapa_status' => ChapaStatus::Pending,
            'is_correction' => false,
            'notes' => 'M-Pesa STK Push — '.$period,
        ]);

        try {
            $response = Http::withToken($token)
                ->acceptJson()
                ->timeout(15)
                ->post("{$this->baseUrl}/mpesa/stkpush/v1/processrequest", [
                    'BusinessShortCode' => $this->shortcode,
                    'Password' => $password,
                    'Timestamp' => $timestamp,
                    'TransactionType' => 'CustomerPayBillOnline',
                    'Amount' => $mpesaAmount,
                    'PartyA' => $phone,
                    'PartyB' => $this->shortcode,
                    'PhoneNumber' => $phone,
                    'CallBackURL' => $callbackUrl ?? $this->callbackUrl ?? url('/api/mpesa/webhook'),
                    'AccountReference' => $txRef,
                    'TransactionDesc' => 'Idir Contribution '.$period,
                ]);

            $json = $response->json() ?? [];

            if ($response->failed() || ($json['ResponseCode'] ?? '') !== '0') {
                Log::warning('MpesaDriver: STK Push failed', ['response' => $json]);

                return ['status' => 'error', 'message' => $json['errorMessage'] ?? $json['ResponseDescription'] ?? 'M-Pesa STK Push failed'];
            }

            $pendingContribution->update(['gateway_reference' => $json['CheckoutRequestID'] ?? null]);

            Log::info('MpesaDriver: STK Push initiated', ['tx_ref' => $txRef, 'checkout_request_id' => $json['CheckoutRequestID'] ?? null]);

            return [
                'status' => 'success',
                'data' => [
                    'tx_ref' => $txRef,
                    'checkout_request_id' => $json['CheckoutRequestID'] ?? null,
                    'message' => $json['CustomerMessage'] ?? 'Check your phone for M-Pesa prompt.',
                ],
            ];
        } catch (\Throwable $e) {
            Log::error('MpesaDriver: network error', ['error' => $e->getMessage()]);

            return ['status' => 'error', 'message' => 'M-Pesa unreachable: '.$e->getMessage()];
        }
    }

    public function verify(string $txRef): array
    {
        // M-Pesa verification is webhook-driven via the callback URL.
        // A direct query endpoint exists but is not needed in the current flow.
        return ['status' => 'success', 'data' => ['status' => 'pending', 'tx_ref' => $txRef]];
    }

    protected function getAccessToken(): string
    {
        $response = Http::withBasicAuth($this->consumerKey, $this->consumerSecret)
            ->timeout(10)
            ->get("{$this->baseUrl}/oauth/v1/generate", ['grant_type' => 'client_credentials']);

        if ($response->failed()) {
            throw new \RuntimeException('M-Pesa OAuth failed: '.$response->body());
        }

        return $response->json('access_token') ?? throw new \RuntimeException('M-Pesa: no access_token in response');
    }

    protected function normalizeMpesaPhone(string $phone): string
    {
        $cleaned = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($cleaned, '07')) {
            return '2517'.substr($cleaned, 2);
        }
        if (str_starts_with($cleaned, '251')) {
            return $cleaned;
        }

        return $cleaned;
    }

    /**
     * Generate the M-Pesa password.
     * In Safaricom Ethiopia: base64(hex_sha256(ShortCode + PassKey + Timestamp)).
     * Requires MPESA_PASSKEY to be explicitly configured.
     */
    protected function generatePassword(string $timestamp): string
    {
        if (empty($this->passkey)) {
            throw new \InvalidArgumentException('M-Pesa passkey (MPESA_PASSKEY) is not configured.');
        }

        $hash = hash('sha256', $this->shortcode.$this->passkey.$timestamp);

        return base64_encode($hash);
    }
}
