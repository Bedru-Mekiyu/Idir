<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class EthiopianPhone implements ValidationRule
{
    /**
     * Run the validation rule.
     * Validates Ethiopian mobile phone number formats:
     * - Local: 09xxxxxxxx or 07xxxxxxxx (10 digits)
     * - International: +2519xxxxxxxx or +2517xxxxxxxx (13 chars)
     * - National without leading 0: 2519xxxxxxxx or 2517xxxxxxxx (12 digits)
     * - Short: 9xxxxxxxx or 7xxxxxxxx (9 digits)
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value)) {
            $fail(__('validation.phone_et'));
            return;
        }

        $cleaned = preg_replace('/[^0-9+]/', '', trim($value));

        // Regex pattern for valid Ethiopian mobile numbers (Ethio Telecom 09/071, Safaricom 07)
        $pattern = '/^(?:\+251|251|0)?(9\d{8}|7\d{8})$/';

        if (!preg_match($pattern, $cleaned)) {
            $fail(__('validation.phone_et') . ' (ትክክለኛ የኢትዮጵያ ሞባይል ስልክ ቁጥር ያስገቡ፣ ለምሳሌ 0911223344 ወይም 0711223344)');
        }
    }
}
