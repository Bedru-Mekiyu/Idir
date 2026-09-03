<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Disbursement extends Model
{
    use HasFactory;

    protected $fillable = [
        'idir_id',
        'claim_id',
        'member_id',
        'amount',
        'method',
        'recorded_by_member_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    public function idir(): BelongsTo
    {
        return $this->belongsTo(Idir::class);
    }

    public function claim(): BelongsTo
    {
        return $this->belongsTo(Claim::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'recorded_by_member_id');
    }
}
