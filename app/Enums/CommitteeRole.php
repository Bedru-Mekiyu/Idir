<?php

namespace App\Enums;

enum CommitteeRole: string
{
    case Chair = 'chair';
    case Secretary = 'secretary';
    case Treasurer = 'treasurer';

    public function label(): string
    {
        return match ($this) {
            self::Chair => __('member.role.chair'),
            self::Secretary => __('member.role.secretary'),
            self::Treasurer => __('member.role.treasurer'),
        };
    }
}
