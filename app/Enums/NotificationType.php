<?php

namespace App\Enums;

enum NotificationType: string
{
    case DueReminder = 'due_reminder';
    case LateWarning = 'late_warning';
    case PaymentConfirmation = 'payment_confirmation';
    case ClaimFiled = 'claim_filed';
    case ClaimApproved = 'claim_approved';
    case ClaimRejected = 'claim_rejected';
    case DisbursementMade = 'disbursement_made';
    case ExclusionWarning = 'exclusion_warning';
    case GeneralAnnouncement = 'general_announcement';
    case NewMemberWelcome = 'new_member_welcome';
    case IdirApproved = 'idir_approved';
    case IdirRejected = 'idir_rejected';

    public function label(): string
    {
        return __('notification.type.'.$this->value);
    }
}
