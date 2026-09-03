<?php

namespace App\Models;

use App\Enums\ApprovalDecision;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClaimApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'claim_id',
        'approver_member_id',
        'decision',
        'approved_amount',
        'remarks',
        'decided_at',
    ];

    protected function casts(): array
    {
        return [
            'decision' => ApprovalDecision::class,
            'approved_amount' => 'decimal:2',
            'decided_at' => 'datetime',
        ];
    }

    public function claim(): BelongsTo
    {
        return $this->belongsTo(Claim::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'approver_member_id');
    }
}
