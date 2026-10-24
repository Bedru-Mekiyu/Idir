<?php

namespace App\Enums;

enum NotificationChannel: string
{
    case Sms = 'sms';
    case Telegram = 'telegram';
    case InApp = 'in_app';
    case Email = 'email';

    public function label(): string
    {
        return __('notification.channel.'.$this->value);
    }
}
