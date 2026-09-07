<?php

namespace App\Services\Payments\Drivers;

use App\Enums\ChapaStatus;
use App\Enums\ContributionType;
use App\Enums\PaymentMethod;
use App\Models\Contribution;
use App\Models\Member;
use App\Services\Payments\PaymentGatewayInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ChapaDriver implements PaymentGatewayInterface
{
    protected string $baseUrl;
    protected ?string $secretKey;

    public function __construct()
    {
        $this->baseUrl = config('services.chapa.base_url', 'https://api.chapa.co/v1');
        $this->secretKey = config('services.chapa.secret_key');
    }

    public function initialize(Member $member, float $amount, string $period, ?string $callbackUrl = null, ?string $returnUrl = null): array
    {
        $txRef = 'IDIR-'.$member->idir_id.'-'.$member->id.'-'.Str::uuid();

        // Create pending contribution row in the ledger
        Contribution::create([
            'idir_id' => $member->idir_id,
            'member_id' => $member->id,
            'amount' => $amount,
            'method' => PaymentMethod::Chapa,
            'type' => ContributionType::Cash,
            'period_covered' => $period,
            'chapa_tx_ref' => $txRef,
            'chapa_status' => ChapaStatus::Pending,
            'is_correction' => false,
            'notes' => __('contribution.period_covered').': '.$period,
        ]);

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
                'description' => __('contribution.period_covered').': '.$period,
            ],
        ];

        $response = Http::withToken($this->secretKey)
            ->acceptJson()
            ->post("{$this->baseUrl}/transaction/initialize", $payload);

        return $response->json();
    }

    public function verify(string $txRef): array
    {
        $response = Http::withToken($this->secretKey)
            ->acceptJson()
            ->get("{$this->baseUrl}/transaction/verify/{$txRef}");

        return $response->json();
    }
}
