<?php

namespace App\Services\Sms\Concerns;

trait NormalizesEthiopianPhone
{
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
