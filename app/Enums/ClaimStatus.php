<?php

namespace App\Enums;

enum ClaimStatus: string
{
    case Pending = 'pending';
    case UnderReview = 'under_review';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Paid = 'paid';

    public function label(): string
    {
        return match ($this) {
            self::Pending => __('claim.status.pending'),
            self::UnderReview => __('claim.status.under_review'),
            self::Approved => __('claim.status.approved'),
            self::Rejected => __('claim.status.rejected'),
            self::Paid => __('claim.status.paid'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'gray',
            self::UnderReview => 'warning',
            self::Approved => 'success',
            self::Rejected => 'danger',
            self::Paid => 'info',
        };
    }
}
