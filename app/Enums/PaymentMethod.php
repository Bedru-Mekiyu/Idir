<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Cash = 'cash';
    case Chapa = 'chapa';

    public function label(): string
    {
        return match ($this) {
            self::Cash => __('contribution.method.cash'),
            self::Chapa => __('contribution.method.chapa'),
        };
    }
}
