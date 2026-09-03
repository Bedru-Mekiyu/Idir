<?php

namespace App\Enums;

enum DocumentCategory: string
{
    case Bylaws = 'bylaws';
    case Receipt = 'receipt';
    case Correspondence = 'correspondence';
    case Other = 'other';

    public function label(): string
    {
        return __('document.categories.' . $this->value);
    }
}
