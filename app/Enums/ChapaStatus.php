<?php

namespace App\Enums;

enum ChapaStatus: string
{
    case Pending = 'pending';
    case Verified = 'verified';
    case Failed = 'failed';
    case Expired = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::Pending => __('contribution.pending'),
            self::Verified => __('contribution.verified'),
            self::Failed => __('contribution.failed'),
            self::Expired => __('contribution.expired'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Verified => 'success',
            self::Failed => 'danger',
            self::Expired => 'gray',
        };
    }
}
