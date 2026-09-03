<?php

namespace App\Models;

use App\Enums\CommitteeRole;
use App\Enums\MemberStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Member extends Model
{
    use HasFactory;

    protected $fillable = [
        'idir_id',
        'user_id',
        'full_name',
        'phone',
        'fayda_id',
        'fayda_verified',
        'join_date',
        'status',
        'committee_role',
        'exclusion_reason',
        'excluded_at',
        'exclusion_warning_sent_at',
        'telegram_chat_id',
    ];

    protected function casts(): array
    {
        return [
            'join_date' => 'date',
            'status' => MemberStatus::class,
            'committee_role' => CommitteeRole::class,
            'fayda_verified' => 'boolean',
            'excluded_at' => 'datetime',
            'exclusion_warning_sent_at' => 'datetime',
        ];
    }

    public function idir(): BelongsTo
    {
        return $this->belongsTo(Idir::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function contributions(): HasMany
    {
        return $this->hasMany(Contribution::class, 'member_id');
    }

    public function paidContributions(): HasMany
    {
        return $this->hasMany(Contribution::class, 'paid_by_member_id');
    }

    public function recordedContributions(): HasMany
    {
        return $this->hasMany(Contribution::class, 'recorded_by_member_id');
    }

    public function claims(): HasMany
    {
        return $this->hasMany(Claim::class, 'member_id');
    }

    public function claimApprovals(): HasMany
    {
        return $this->hasMany(ClaimApproval::class, 'approver_member_id');
    }

    public function disbursements(): HasMany
    {
        return $this->hasMany(Disbursement::class, 'member_id');
    }

    public function meetings(): HasMany
    {
        return $this->hasMany(Meeting::class, 'recorded_by_member_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'uploaded_by_member_id');
    }

    public function notificationEvents(): HasMany
    {
        return $this->hasMany(NotificationEvent::class, 'member_id');
    }

    public function isCommitteeMember(): bool
    {
        return !is_null($this->committee_role);
    }

    public function isChair(): bool
    {
        return $this->committee_role === CommitteeRole::Chair;
    }

    public function isTreasurer(): bool
    {
        return $this->committee_role === CommitteeRole::Treasurer;
    }

    public function isSecretary(): bool
    {
        return $this->committee_role === CommitteeRole::Secretary;
    }

    public function isVested(): bool
    {
        if (!$this->relationLoaded('idir') || !$this->idir->relationLoaded('settings')) {
            $this->loadMissing('idir.settings');
        }

        $vestingDays = $this->idir?->settings?->vesting_period_days ?? 90;
        return Carbon::parse($this->join_date)->diffInDays(now()) >= $vestingDays;
    }
}
