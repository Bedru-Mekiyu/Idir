<?php

namespace App\Enums;

enum ContributionType: string
{
    case Cash = 'cash';
    case InKind = 'in_kind';

    public function label(): string
    {
        return match ($this) {
            self::Cash => __('contribution.type.cash'),
            self::InKind => __('contribution.type.in_kind'),
        };
    }
}
