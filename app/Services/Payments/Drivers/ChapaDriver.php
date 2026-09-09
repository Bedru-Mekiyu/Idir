<?php

namespace App\Services\Payments\Drivers;

use App\Enums\ChapaStatus;
use App\Enums\ContributionType;
use App\Enums\PaymentMethod;
use App\Models\Contribution;
use App\Models\IdirSetting;
use App\Models\Member;
use App\Services\Payments\PaymentGatewayInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
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

    /**
     * Initiate a hosted-checkout transaction. The member is redirected to
     * Chapa's checkout page, which supports every method Chapa offers.
     *
     * When the idir has a Chapa subaccount configured, the transaction is
     * routed to that subaccount (split payment).
     */
    public function initialize(Member $member, float $amount, string $period, ?string $callbackUrl = null, ?string $returnUrl = null): array
    {
        $txRef = 'IDIR-'.$member->idir_id.'-'.$member->id.'-'.Str::uuid();

        $this->createPendingContribution($member, $amount, $period, PaymentMethod::Chapa, $txRef);

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

        if ($subaccountId = $this->subaccountIdFor($member)) {
            // Hosted checkout sends a JSON body; Chapa expects a nested object.
            $payload['subaccounts'] = ['id' => $subaccountId];
        }

        $response = Http::withToken($this->secretKey)
            ->acceptJson()
            ->post("{$this->baseUrl}/transaction/initialize", $payload);

        return $response->json();
    }

    /**
     * Initiate a Direct Charge for a specific payment method (Chapa's
     * `charges?type={method}` endpoint). The customer authorizes the charge on
     * their own phone via USSD push (Telebirr, CBEBirr), so no redirect
     * happens; confirmation arrives via Chapa's webhook and is verified
     * server-side with {@see verify()}.
     *
     * The pending ledger entry is created first and only marked failed if
     * Chapa rejects the charge outright, so the ledger never trusts a
     * client-side status.
     *
     * @param  string  $chapaType  Chapa `type` query value (telebirr, cbebirr).
     */
    public function initializeDirectCharge(Member $member, float $amount, string $period, string $chapaType, string $mobile, ?string $callbackUrl = null): array
    {
        $method = PaymentMethod::tryFrom($chapaType);

        if ($method === null || $method->chapaDirectChargeType() === null) {
            throw new \InvalidArgumentException("Unsupported Chapa direct charge type: {$chapaType}");
        }

        $txRef = 'IDIR-'.$member->idir_id.'-'.$member->id.'-'.Str::uuid();

        $contribution = $this->createPendingContribution($member, $amount, $period, $method, $txRef);

        $names = explode(' ', trim($member->full_name), 2);
        $firstName = $names[0] ?? $member->full_name;
        $lastName = $names[1] ?? 'አባል';

        $payload = [
            'amount' => (string) $amount,
            'currency' => 'ETB',
            'tx_ref' => $txRef,
            'mobile' => $mobile,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $member->user?->email,
            'callback_url' => $callbackUrl ?? url('/api/chapa/webhook'),
        ];

        if ($subaccountId = $this->subaccountIdFor($member)) {
            // Direct Charge sends form-encoded fields; the subaccounts object
            // is carried as a JSON-encoded field value.
            $payload['subaccounts'] = json_encode(['id' => $subaccountId]);
        }

        try {
            $response = Http::withToken($this->secretKey)
                ->asForm()
                ->timeout(15)
                ->post("{$this->baseUrl}/charges?type={$chapaType}", $payload);

            $json = $response->json() ?? [];

            if ($response->failed() || ($json['status'] ?? '') !== 'success') {
                Log::warning('Chapa Direct Charge: charge rejected', [
                    'tx_ref' => $txRef,
                    'type' => $chapaType,
                    'status' => $response->status(),
                    'response' => $json,
                ]);

                $contribution->update(['chapa_status' => ChapaStatus::Failed]);

                return [
                    'status' => 'failed',
                    'message' => $json['message'] ?? 'Chapa charge rejected',
                    'data' => $json['data'] ?? null,
                ];
            }

            Log::info('Chapa Direct Charge: USSD charge initiated', [
                'tx_ref' => $txRef,
                'type' => $chapaType,
                'response' => $json,
            ]);

            return $json;
        } catch (\Throwable $e) {
            Log::error('Chapa Direct Charge: network error', [
                'tx_ref' => $txRef,
                'type' => $chapaType,
                'error' => $e->getMessage(),
            ]);

            $contribution->update(['chapa_status' => ChapaStatus::Failed]);

            return [
                'status' => 'failed',
                'message' => 'Chapa unreachable: '.$e->getMessage(),
                'data' => null,
            ];
        }
    }

    public function verify(string $txRef): array
    {
        $response = Http::withToken($this->secretKey)
            ->acceptJson()
            ->get("{$this->baseUrl}/transaction/verify/{$txRef}");

        return $response->json();
    }

    /**
     * Reserve the ledger entry as pending for the given payment method.
     */
    protected function createPendingContribution(Member $member, float $amount, string $period, PaymentMethod $method, string $txRef): Contribution
    {
        return Contribution::create([
            'idir_id' => $member->idir_id,
            'member_id' => $member->id,
            'amount' => $amount,
            'method' => $method,
            'type' => ContributionType::Cash,
            'period_covered' => $period,
            'chapa_tx_ref' => $txRef,
            'chapa_status' => ChapaStatus::Pending,
            'is_correction' => false,
            'notes' => __('contribution.period_covered').': '.$period.' ('.$method->label().')',
        ]);
    }

    /**
     * The idir's Chapa subaccount id (split payment routing), if configured.
     */
    protected function subaccountIdFor(Member $member): ?string
    {
        return IdirSetting::where('idir_id', $member->idir_id)->value('chapa_subaccount_id') ?: null;
    }
}
