<?php

namespace App\Models;

use App\Enums\ChapaStatus;
use App\Enums\ContributionType;
use App\Enums\PaymentMethod;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contribution extends Model
{
    use HasFactory;

    protected $fillable = [
        'idir_id',
        'member_id',
        'paid_by_member_id',
        'recorded_by_member_id',
        'amount',
        'method',
        'type',
        'in_kind_description',
        'period_covered',
        'chapa_tx_ref',
        'chapa_status',
        'notes',
        'is_correction',
        'corrected_contribution_id',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'method' => PaymentMethod::class,
            'type' => ContributionType::class,
            'chapa_status' => ChapaStatus::class,
            'is_correction' => 'boolean',
        ];
    }

    public function idir(): BelongsTo
    {
        return $this->belongsTo(Idir::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'member_id');
    }

    public function paidBy(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'paid_by_member_id');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'recorded_by_member_id');
    }

    public function correctedContribution(): BelongsTo
    {
        return $this->belongsTo(Contribution::class, 'corrected_contribution_id');
    }

    public function corrections(): HasMany
    {
        return $this->hasMany(Contribution::class, 'corrected_contribution_id');
    }
}
