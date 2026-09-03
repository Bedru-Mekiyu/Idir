<?php

namespace App\Models;

use App\Enums\NotificationChannel;
use App\Enums\NotificationStatus;
use App\Enums\NotificationType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'idir_id',
        'member_id',
        'type',
        'channel',
        'status',
        'message_content',
        'error_detail',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => NotificationType::class,
            'channel' => NotificationChannel::class,
            'status' => NotificationStatus::class,
            'sent_at' => 'datetime',
        ];
    }

    public function idir(): BelongsTo
    {
        return $this->belongsTo(Idir::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }
}
