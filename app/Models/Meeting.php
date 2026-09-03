<?php

namespace App\Models;

use App\Enums\MeetingType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Meeting extends Model
{
    use HasFactory;

    protected $fillable = [
        'idir_id',
        'type',
        'meeting_date',
        'minutes',
        'attachment_path',
        'recorded_by_member_id',
    ];

    protected function casts(): array
    {
        return [
            'type' => MeetingType::class,
            'meeting_date' => 'date',
        ];
    }

    public function idir(): BelongsTo
    {
        return $this->belongsTo(Idir::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'recorded_by_member_id');
    }
}
