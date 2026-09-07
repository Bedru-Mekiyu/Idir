<?php

namespace App\Models;

use App\Enums\ClaimStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Claim extends Model
{
    use HasFactory;

    protected $fillable = [
        'idir_id',
        'member_id',
        'payout_trigger_type_id',
        'description',
        'requested_amount',
        'status',
        'document_path',
    ];

    protected function casts(): array
    {
        return [
            'requested_amount' => 'decimal:2',
            'status' => ClaimStatus::class,
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

    public function triggerType(): BelongsTo
    {
        return $this->belongsTo(PayoutTriggerType::class, 'payout_trigger_type_id');
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(ClaimApproval::class);
    }

    public function disbursement(): HasOne
    {
        return $this->hasOne(Disbursement::class);
    }

    /**
     * Fix #3 Disbursement amount fallback logic:
     * 1. If claims.requested_amount was provided -> default proposed amount.
     * 2. If null -> fall back to payout_trigger_types.default_payout_amount.
     * 3. If both null -> null (approver must enter manually).
     */
    public function getProposedAmount(): ?float
    {
        if (! is_null($this->requested_amount)) {
            return (float) $this->requested_amount;
        }

        if ($this->triggerType && ! is_null($this->triggerType->default_payout_amount)) {
            return (float) $this->triggerType->default_payout_amount;
        }

        return null;
    }
}
