<?php

namespace App\Models;

use App\Enums\DocumentCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'idir_id',
        'title',
        'file_path',
        'category',
        'uploaded_by_member_id',
        'deletion_requested',
        'deletion_confirmed',
        'deletion_confirmed_by',
    ];

    protected function casts(): array
    {
        return [
            'category' => DocumentCategory::class,
            'deletion_requested' => 'boolean',
            'deletion_confirmed' => 'boolean',
        ];
    }

    public function idir(): BelongsTo
    {
        return $this->belongsTo(Idir::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'uploaded_by_member_id');
    }

    public function deletionConfirmedBy(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'deletion_confirmed_by');
    }
}
