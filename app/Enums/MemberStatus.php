<?php

namespace App\Enums;

enum MemberStatus: string
{
    case Active = 'active';
    case InArrears = 'in_arrears';
    case Excluded = 'excluded';
    case Inactive = 'inactive';

    public function label(): string
    {
        return match ($this) {
            self::Active => __('member.status.active'),
            self::InArrears => __('member.status.in_arrears'),
            self::Excluded => __('member.status.excluded'),
            self::Inactive => __('member.status.inactive'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Active => 'success',
            self::InArrears => 'warning',
            self::Excluded => 'danger',
            self::Inactive => 'gray',
        };
    }
}
