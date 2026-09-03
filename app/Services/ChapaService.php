<?php

namespace App\Services;

use App\Enums\ChapaStatus;
use App\Enums\ContributionType;
use App\Enums\PaymentMethod;
use App\Models\Contribution;
use App\Models\Member;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ChapaService
{
    protected string $baseUrl;
    protected ?string $secretKey;

    public function __construct()
    {
        $this->baseUrl = config('services.chapa.base_url', 'https://api.chapa.co/v1');
        $this->secretKey = config('services.chapa.secret_key');
    }

    /**
     * Initialize a digital payment session with Chapa.
     */
    public function initializePayment(Member $member, float $amount, string $period, ?string $callbackUrl = null, ?string $returnUrl = null): array
    {
        $txRef = 'IDIR-' . $member->idir_id . '-' . $member->id . '-' . Str::uuid();

        // Create pending contribution row in the ledger
        $contribution = Contribution::create([
            'idir_id' => $member->idir_id,
            'member_id' => $member->id,
            'amount' => $amount,
            'method' => PaymentMethod::Chapa,
            'type' => ContributionType::Cash,
            'period_covered' => $period,
            'chapa_tx_ref' => $txRef,
            'chapa_status' => ChapaStatus::Pending,
            'is_correction' => false,
            'notes' => __('contribution.period_covered') . ': ' . $period,
        ]);

        if (empty($this->secretKey)) {
            // Mock response for local development/testing without real Chapa keys
            return [
                'status' => 'success',
                'message' => 'Hosted Link (Mock)',
                'data' => [
                    'checkout_url' => url("/mock/chapa/checkout/{$txRef}"),
                    'tx_ref' => $txRef,
                ],
            ];
        }

        $names = explode(' ', trim($member->full_name), 2);
        $firstName = $names[0] ?? $member->full_name;
        $lastName = $names[1] ?? 'አባል';

        $payload = [
            'amount' => (string) $amount,
            'currency' => 'ETB',
            'phone_number' => $member->phone,
            'tx_ref' => $txRef,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'callback_url' => $callbackUrl ?? url('/api/chapa/webhook'),
            'return_url' => $returnUrl ?? url('/payment/success'),
            'customization' => [
                'title' => $member->idir->name ?? 'የእድር መዋጮ',
                'description' => __('contribution.period_covered') . ': ' . $period,
            ],
        ];

        $response = Http::withToken($this->secretKey)
            ->acceptJson()
            ->post("{$this->baseUrl}/transaction/initialize", $payload);

        return $response->json();
    }

    /**
     * Server-side transaction verification via Chapa API.
     * Never trust client-side redirect parameters alone.
     */
    public function verifyPayment(string $txRef): array
    {
        if (empty($this->secretKey)) {
            // Mock verification for local testing
            return [
                'status' => 'success',
                'message' => 'Payment details (Mock)',
                'data' => [
                    'status' => 'success',
                    'tx_ref' => $txRef,
                    'currency' => 'ETB',
                ],
            ];
        }

        $response = Http::withToken($this->secretKey)
            ->acceptJson()
            ->get("{$this->baseUrl}/transaction/verify/{$txRef}");

        return $response->json();
    }
}
