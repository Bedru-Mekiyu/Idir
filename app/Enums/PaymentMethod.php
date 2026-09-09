<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Cash = 'cash';
    case Chapa = 'chapa';
    case Telebirr = 'telebirr';
    case CBEBirr = 'cbebirr';

    public function label(): string
    {
        return match ($this) {
            self::Cash => __('contribution.method.cash'),
            self::Chapa => __('contribution.method.chapa'),
            self::Telebirr => __('contribution.method.telebirr'),
            self::CBEBirr => __('contribution.method.cbebirr'),
        };
    }

    /**
     * The value Chapa's Direct Charge API expects in the `type` query parameter.
     * Only methods that Chapa can charge directly via its own gateway qualify;
     * cash is a manually recorded method and has no Chapa type.
     */
    public function chapaDirectChargeType(): ?string
    {
        return match ($this) {
            self::Telebirr => 'telebirr',
            self::CBEBirr => 'cbebirr',
            self::Chapa => null,
            self::Cash => null,
        };
    }
}
