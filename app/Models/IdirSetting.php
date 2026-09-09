<?php

namespace App\Models;

use App\Enums\DuesFrequency;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IdirSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'idir_id',
        'dues_amount',
        'dues_frequency',
        'late_fee_amount',
        'late_fee_grace_days',
        'vesting_period_days',
        'required_approvals',
        'enabled_payout_triggers',
        'fund_balance',
        'chapa_subaccount_id',
    ];

    protected function casts(): array
    {
        return [
            'dues_amount' => 'decimal:2',
            'dues_frequency' => DuesFrequency::class,
            'late_fee_amount' => 'decimal:2',
            'late_fee_grace_days' => 'integer',
            'vesting_period_days' => 'integer',
            'required_approvals' => 'integer',
            'enabled_payout_triggers' => 'array',
            'fund_balance' => 'decimal:2',
        ];
    }

    public function idir(): BelongsTo
    {
        return $this->belongsTo(Idir::class);
    }
}
