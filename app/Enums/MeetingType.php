<?php

namespace App\Enums;

enum MeetingType: string
{
    case GeneralAssembly = 'general_assembly';
    case Executive = 'executive';
    case Trustee = 'trustee';

    public function label(): string
    {
        return __('meeting.type.'.$this->value);
    }
}
