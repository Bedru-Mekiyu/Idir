<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayoutTriggerType extends Model
{
    use HasFactory;

    protected $fillable = [
        'idir_id',
        'name',
        'label_am',
        'default_payout_amount',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'default_payout_amount' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function idir(): BelongsTo
    {
        return $this->belongsTo(Idir::class);
    }

    public function claims(): HasMany
    {
        return $this->hasMany(Claim::class);
    }
}
