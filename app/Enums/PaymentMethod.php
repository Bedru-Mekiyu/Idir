<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Cash = 'cash';
    case Chapa = 'chapa';
    case Telebirr = 'telebirr';

    public function label(): string
    {
        return match ($this) {
            self::Cash => __('contribution.method.cash'),
            self::Chapa => __('contribution.method.chapa'),
            self::Telebirr => __('contribution.method.telebirr', default: 'Telebirr'),
        };
    }
}
