<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContributionRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'idir_id',
        'category_name',
        'amount',
        'frequency',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'is_default' => 'boolean',
        ];
    }

    public function idir(): BelongsTo
    {
        return $this->belongsTo(Idir::class);
    }
}
