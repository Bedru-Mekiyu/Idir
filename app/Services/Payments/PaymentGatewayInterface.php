<?php

namespace App\Services\Payments;

use App\Models\Member;

interface PaymentGatewayInterface
{
    public function initialize(Member $member, float $amount, string $period, ?string $callbackUrl = null, ?string $returnUrl = null): array;

    public function verify(string $txRef): array;
}
