<?php

namespace App\Enums;

enum DuesFrequency: string
{
    case Weekly = 'weekly';
    case Monthly = 'monthly';
    case Quarterly = 'quarterly';
    case Annually = 'annually';

    public function label(): string
    {
        return match ($this) {
            self::Weekly => __('idir.frequency.weekly'),
            self::Monthly => __('idir.frequency.monthly'),
            self::Quarterly => __('idir.frequency.quarterly'),
            self::Annually => __('idir.frequency.annually'),
        };
    }
}
