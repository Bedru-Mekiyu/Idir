<?php

namespace App\Models;

use App\Enums\NotificationType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'idir_id',
        'event_type',
        'sms_enabled',
        'telegram_enabled',
        'in_app_enabled',
        'template_am',
        'template_en',
    ];

    protected function casts(): array
    {
        return [
            'event_type' => NotificationType::class,
            'sms_enabled' => 'boolean',
            'telegram_enabled' => 'boolean',
            'in_app_enabled' => 'boolean',
        ];
    }

    public function idir(): BelongsTo
    {
        return $this->belongsTo(Idir::class);
    }
}
