<?php

namespace App\Enums;

enum NotificationStatus: string
{
    case Queued = 'queued';
    case Sent = 'sent';
    case Delivered = 'delivered';
    case Failed = 'failed';

    public function label(): string
    {
        return __('notification.status.'.$this->value);
    }
}
